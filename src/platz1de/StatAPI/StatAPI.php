<?php

namespace platz1de\StatAPI;

use platz1de\StatAPI\command\StatAdminCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use platz1de\StatAPI\command\LeaderboardCommand;
use platz1de\StatAPI\command\StatsCommand;

class StatAPI extends PluginBase
{
	private static StatAPI $instance;
	private DataConnector $database;
	/**
	 * @var Module[]
	 */
	private array $modules = [];

	public function onLoad(): void
	{
		self::$instance = $this;
		$this->loadConfig(1);
		$this->initDatabase();
		$this->reload();

		$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
			$this->reload();
		}), $this->getConfig()->get("reload-duration", 1200));

		$this->getServer()->getCommandMap()->registerAll("statapi", [new StatsCommand(), new LeaderboardCommand(), new StatAdminCommand()]);
	}

	/**
	 * @return StatAPI
	 */
	public static function getInstance(): StatAPI
	{
		return self::$instance;
	}

	/**
	 * @return string
	 */
	public static function getPrefix(): string
	{
		return self::getInstance()->getConfig()->get("prefix", "§4Stats §f> ");
	}

	/**
	 * @param Module $module
	 * @internal use Module::get instead
	 * @see      Module::get
	 */
	public function registerModule(Module $module): void
	{
		$this->modules[strtolower($module->getName())] = $module;
	}

	/**
	 * @param Module $module
	 * @internal
	 */
	public function unregisterModule(Module $module): void
	{
		unset($this->modules[strtolower($module->getName())]);
	}

	/**
	 * @return Module
	 */
	public function getDefaultModule(): Module
	{
		return Module::get($this->getConfig()->get("server-module", "default"));
	}

	/**
	 * @param string $name
	 * @param bool   $exact
	 * @return Module|null
	 *
	 * Only returns Module if it exists
	 * @see Module::get to create a Module
	 */
	public function getModule(string $name, bool $exact = true): ?Module
	{
		if ($exact) {
			return $this->modules[strtolower($name)] ?? null;
		}

		$match = null;
		foreach ($this->modules as $module) {
			if ($match === null || strlen($match) > strlen($module->getName()) || strlen($match) > strlen($module->getDisplayName())) {
				if (str_starts_with(strtolower($module->getName()), strtolower($name)) || str_starts_with(strtolower($module->getDisplayName()), strtolower($name))) {
					$match = $module;
				}
			}
		}
		return $match;
	}

	/**
	 * @return Module[]
	 */
	public function getModules(): array
	{
		return $this->modules;
	}

	/**
	 * @param string      $name
	 * @param null|Module $module
	 * @param bool        $exact
	 * @return Stat|null
	 *
	 * Only returns Stat if it exists
	 * @see Stat::get to create a Stat
	 */
	public function getStat(string $name, ?Module $module = null, bool $exact = true): ?Stat
	{
		if ($module === null) {
			return null;
		}

		if ($exact) {
			return $module->getStats()[strtolower($name)] ?? null;
		}

		$match = null;
		foreach ($module->getStats() as $stat) {
			if ($match === null || strlen($match) > strlen($stat->getName()) || strlen($match) > strlen($stat->getDisplayName())) {
				if (str_starts_with(strtolower($stat->getName()), strtolower($name)) || str_starts_with(strtolower($stat->getDisplayName()), strtolower($name))) {
					$match = $stat;
				}
			}
		}
		return $match;
	}

	private function initDatabase(): void
	{
		$this->database = libasynql::create($this, $this->getConfig()->get("database"), ["mysql" => "mysql.sql"]);

		$this->database->executeGeneric(Query::INIT_MODULES_TABLE);
		$this->database->executeGeneric(Query::INIT_STATS_TABLE);
		$this->database->executeGeneric(Query::INIT_DATA_TABLE);
		$this->database->waitAll();
	}

	public function reload(): void
	{
		$this->database->executeSelect(Query::GET_MODULES, [], function (array $rows) {
			$registered = $this->modules;
			foreach ($rows as $row) {
				if (($m = $this->getModule($row["name"])) instanceof Module) {
					$m->setDisplayName($row["displayName"] ?? "", false);
					$m->setVisible($row["visible"] ?? true, false);
					unset($registered[$row["name"]]);
				} else {
					$this->registerModule(new Module($row["name"], $row["displayName"] ?? "", $row["visible"] ?? true));
				}
			}
			foreach ($registered as $remove) {
				$this->unregisterModule($remove);
			}
			$this->database->executeSelect(Query::GET_STATS, [], function (array $rows) {
				$registered = array_map(static function (Module $m): array { return $m->getStats(); }, $this->modules);
				foreach ($rows as $row) {
					if (($module = $this->getModule($row["module"])) instanceof Module) {
						if (($s = $this->getStat($row["name"], $module)) instanceof Stat) {
							$s->setType($row["type"] ?? 0, false);
							$s->setDisplayType($row["displayType"] ?? 0, false);
							$s->setDefault($row["defaultValue"] ?? "0", false);
							$s->setDisplayName($row["displayName"] ?? "", false);
							$s->setVisible($row["visible"] ?? true, false);
							unset($registered[strtolower($row["module"])][strtolower($row["name"])]);
						} else {
							$module->addStat(new Stat($row["name"], $module, $row["type"] ?? 0, $row["displayType"] ?? 0, $row["defaultValue"] ?? "0", $row["displayName"] ?? "", $row["visible"] ?? true));
						}
					}
				}
				foreach ($registered as $m => $i) {
					foreach ($i as $remove) {
						$this->getModule($m)?->removeStat($remove);
					}
				}
				$this->reloadAllData();
			});
		});
	}

	public function reloadAllData(): void
	{
		$this->database->executeSelect(Query::GET_DATA, [], function (array $rows) {
			//Data rows will never get removed from cache to allow non-saving stat constructs
			foreach ($rows as $row) {
				$stat = $this->getStat($row["stat"], $this->getModule($row["module"]));
				if ($stat instanceof Stat) {
					$stat->setScore($row["player"], $row["score"], false);
				}
			}
			foreach ($this->modules as $module) {
				foreach ($module->getStats() as $stat) {
					if ($stat->getType() === Stat::TYPE_RATIO) {
						$first = $this->getStat(explode("//", $stat->getDefault())[0], $module);
						$second = $this->getStat(explode("//", $stat->getDefault())[1] ?? "", $module);

						if ($first instanceof Stat && $second instanceof Stat && $first->getType() !== Stat::TYPE_RATIO && $second->getType() !== Stat::TYPE_RATIO) { //Don't allow recursive ratio stat, so we don't have to deal with the possible problems
							foreach (array_unique(array_merge(array_keys($first->getData()), array_keys($second->getData()))) as $player) {
								$stat->setScore($player, ((int) $first->getScore($player)) / max(1, (int) $second->getScore($player)), false);
							}
						} else {
							$this->getLogger()->warning("Couldn't find stats for ratio-Stat '" . $stat->getName() . "' (" . $stat->getDefault() . ")");
						}
					}
					$stat->sort();
				}
			}
		});
	}

	/**
	 * @return DataConnector
	 * @internal
	 */
	public function getDatabase(): DataConnector
	{
		return $this->database;
	}

	public function onDisable(): void
	{
		if (isset($this->database)) {
			$this->database->close();
		}
	}

	/**
	 * @param int $version
	 */
	private function loadConfig(int $version): void
	{
		$this->saveDefaultConfig();
		if ($this->getConfig()->get("version") !== $version) {
			unlink($this->getDataFolder() . "config.yml");
			$this->getLogger()->info("Your config was renewed!");
			$this->saveDefaultConfig();
		}
	}
}
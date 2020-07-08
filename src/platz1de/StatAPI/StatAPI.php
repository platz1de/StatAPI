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
	/**
	 * @var StatAPI
	 */
	private static $instance;
	/**
	 * @var DataConnector
	 */
	private $database;
	/**
	 * @var Module[]
	 */
	private $modules = [];

	public function onLoad()
	{
		self::$instance = $this;
		$this->loadConfig(1);
		$this->initDatabase();
		$this->reload();

		$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currenttick): void {
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

	public static function getPrefix()
	{
		self::getInstance()->getConfig()->get("prefix", "§4Stats §f> ");
	}

	/**
	 * @param Module $module
	 * @internal use Module::get
	 */
	public function registerModule(Module $module)
	{
		$this->modules[strtolower($module->getName())] = $module;
	}

	/**
	 * @param Module $module
	 * @internal
	 */
	public function unregisterModule(Module $module)
	{
		unset($this->modules[strtolower($module->getName())]);
	}

	/**
	 * @return Module
	 */
	public function getDefaultModule()
	{
		return Module::get($this->getConfig()->get("server-module", "default"));
	}

	/**
	 * @param string $name
	 * @param bool $exact
	 * @return Module|null
	 */
	public function getModule(string $name, bool $exact = true)
	{
		if ($exact) {
			return $this->modules[strtolower($name)] ?? null;
		} else {
			$match = null;
			foreach ($this->modules as $module) {
				if ($match === null or strlen($match) > strlen($module->getName()) or strlen($match) > strlen($module->getDisplayName())) {
					if (substr(strtolower($module->getName()), 0, strlen($name)) === $name or substr(strtolower($module->getDisplayName()), 0, strlen($name)) === $name) {
						$match = $module;
					}
				}
			}
			return $match;
		}
	}

	/**
	 * @return Module[]
	 */
	public function getModules()
	{
		return $this->modules;
	}

	/**
	 * @param string $name
	 * @param null|Module $module
	 * @param bool $exact
	 * @return Stat|null
	 */
	public function getStat(string $name, ?Module $module = null, bool $exact = true)
	{
		if ($module === null) {
			return null;
		}
		if ($exact) {
			return $module->getStats()[strtolower($name)] ?? null;
		} else {
			$match = null;
			foreach ($module->getStats() as $stat) {
				if ($match === null or strlen($match) > strlen($stat->getName()) or strlen($match) > strlen($stat->getDisplayName())) {
					if (substr(strtolower($stat->getName()), 0, strlen($name)) === $name or substr(strtolower($stat->getDisplayName()), 0, strlen($name)) === $name) {
						$match = $stat;
					}
				}
			}
			return $match;
		}
	}

	private function initDatabase()
	{
		$this->database = libasynql::create($this, $this->getConfig()->get("database"), ["mysql" => "mysql.sql"]);

		$this->database->executeGeneric(Query::INIT_MODULES_TABLE);
		$this->database->executeGeneric(Query::INIT_STATS_TABLE);
		$this->database->executeGeneric(Query::INIT_DATA_TABLE);
		$this->database->waitAll();
	}

	public function reload()
	{
		$this->database->executeSelect(Query::GET_MODULES, [], function (array $rows) {
			$this->modules = [];
			foreach ($rows as $row) {
				$this->registerModule(new Module($row["name"], $row["displayName"] ?? "", $row["visible"] ?? true));
			}
			$this->database->executeSelect(Query::GET_STATS, [], function (array $rows) {
				$pos = 0;
				foreach ($rows as $row) {
					if (($module = $this->getModule($row["module"])) instanceof Module) {
						$module->addStat(new Stat($row["name"], $module, $row["type"] ?? 0, $row["displayType"] ?? 0, $row["default"] ?? "0", $row["displayName"] ?? "", $row["visible"] ?? true));
					}
					//clear holes in positions
					$this->getDatabase()->executeChange(Query::SET_STAT_POSITION, ["stat" => $row["name"], "module" => $row["module"], "position" => ++$pos]);
				}
				$this->reloadAllData();
			});
		});
	}

	public function reloadAllData()
	{
		$this->database->executeSelect(Query::GET_DATA, [], function (array $rows) {
			foreach ($this->modules as $module) {
				foreach ($module->getStats() as $stat) {
					$stat->resetData(false);
				}
			}
			foreach ($rows as $row) {
				$stat = $this->getStat($row["stat"], $this->getModule($row["module"]));
				if ($stat instanceof Stat) {
					$stat->setScore($row["player"], $row["score"], false);
				}
			}
			foreach ($this->modules as $module) {
				foreach ($module->getStats() as $stat) {
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

	public function onDisable()
	{
		if (isset($this->database)) {
			$this->database->close();
		}
	}

	/**
	 * @param int $version
	 */
	private function loadConfig(int $version)
	{
		$this->saveDefaultConfig();
		if ($this->getConfig()->get("version") !== $version) {
			unlink($this->getDataFolder() . "config.yml");
			$this->getLogger()->info("Your config was renewed!");
			$this->saveDefaultConfig();
		}
	}
}
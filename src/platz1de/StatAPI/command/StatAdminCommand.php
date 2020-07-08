<?php

namespace platz1de\StatAPI\command;

use InvalidArgumentException;
use pocketmine\command\CommandSender;
use platz1de\StatAPI\Module;
use platz1de\StatAPI\Query;
use platz1de\StatAPI\Stat;
use platz1de\StatAPI\StatAPI;

class StatAdminCommand extends CommandBase
{
	/**
	 * LeaderboardCommand constructor.
	 */
	public function __construct()
	{
		parent::__construct("statadmin");
		$this->setPermission("statapi.command.statadmin");
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return mixed|void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return;
		}

		switch (strtolower($args[0] ?? "help")) {
			case "modules":
				$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{modules}"], [implode(", ", $this->getPlugin()->getModules())], StatAPI::getInstance()->getConfig()->get("statadmin-modules", "§eList of all modules:§r\n{modules}")));
				return;
			case "stats":
				if (count($args) > 1 and ($module = $this->getPlugin()->getModule($args[1], false)) instanceof Module) {
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stats}"], [$module->getDisplayName(), implode(", ", $module->getStats())], StatAPI::getInstance()->getConfig()->get("statadmin-stats", "§eList of all stats of module '{module}':§r\n{stats}")));
				} else {
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}"], [$args[1] ?? ""], StatAPI::getInstance()->getConfig()->get("statadmin-module-unknown", "The module '{module}' doesn't exists!")));
				}
				return;
			case "deletemodule":
				if (count($args) > 1 and ($module = $this->getPlugin()->getModule($args[1], false)) instanceof Module) {
					if ($module->getStats() !== []) {
						$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}"], [$module->getName()], StatAPI::getInstance()->getConfig()->get("statadmin-module-has-stats", "The module '{module}' still has stats!")));
						return;
					}
					$this->getPlugin()->getDatabase()->executeChange(Query::UNREGISTER_MODULE, ["module" => $module->getName()], function (int $affectedRows) use ($sender, $module) {
						$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}"], [$module->getName()], StatAPI::getInstance()->getConfig()->get("statadmin-module-deleted", "The module '{module}' was deleted!")));
						$this->getPlugin()->unregisterModule($module);
					});
				} else {
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}"], [$args[1] ?? ""], StatAPI::getInstance()->getConfig()->get("statadmin-module-unknown", "The module '{module}' doesn't exists!")));
				}
				return;
			case "deletestat":
				if (count($args) > 2 and ($stat = $this->getPlugin()->getStat($args[2], $this->getPlugin()->getModule($args[1]))) instanceof Stat) {
					$this->getPlugin()->getDatabase()->executeChange(Query::UNREGISTER_STAT, ["stat" => $stat->getName(), "module" => $stat->getModule()->getName()], function (int $affectedRows) use ($sender, $stat) {
						$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stat}"], [$stat->getModule()->getName(), $stat->getName()], StatAPI::getInstance()->getConfig()->get("statadmin-stat-deleted", "The stat '{stat}' of module '{module}' was deleted!")));
						$stat->getModule()->removeStat($stat);
					});
				} else {
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stat}"], [$args[1] ?? "", $args[2] ?? ""], StatAPI::getInstance()->getConfig()->get("statadmin-stat-unknown", "The stat '{stat}' of module '{module}' doesn't exists!")));
				}
				return;
			case "hidemodule":
				if (count($args) > 1 and ($module = $this->getPlugin()->getModule($args[1], false)) instanceof Module) {
					$module->setVisible(false);
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}"], [$module->getName()], StatAPI::getInstance()->getConfig()->get("statadmin-module-hidden", "The module '{module}' was hidden!")));
				} else {
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}"], [$args[1] ?? ""], StatAPI::getInstance()->getConfig()->get("statadmin-module-unknown", "The module '{module}' doesn't exists!")));
				}
				return;
			case "showmodule":
				if (count($args) > 1 and ($module = $this->getPlugin()->getModule($args[1], false)) instanceof Module) {
					$module->setVisible(true);
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}"], [$module->getName()], StatAPI::getInstance()->getConfig()->get("statadmin-module-shown", "The module '{module}' was shown!")));
				} else {
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}"], [$args[1] ?? ""], StatAPI::getInstance()->getConfig()->get("statadmin-module-unknown", "The module '{module}' doesn't exists!")));
				}
				return;
			case "hidestat":
				if (count($args) > 2 and ($stat = $this->getPlugin()->getStat($args[2], $this->getPlugin()->getModule($args[1]))) instanceof Stat) {
					$stat->setVisible(false);
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stat}"], [$stat->getModule()->getName(), $stat->getName()], StatAPI::getInstance()->getConfig()->get("statadmin-stat-hidden", "The stat '{stat}' of module '{module}' was hidden!")));
				} else {
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stat}"], [$args[1] ?? "", $args[2] ?? ""], StatAPI::getInstance()->getConfig()->get("statadmin-stat-unknown", "The stat '{stat}' of module '{module}' doesn't exists!")));
				}
				return;
			case "showstat":
				if (count($args) > 2 and ($stat = $this->getPlugin()->getStat($args[2], $this->getPlugin()->getModule($args[1]))) instanceof Stat) {
					$stat->setVisible(true);
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stat}"], [$stat->getModule()->getName(), $stat->getName()], StatAPI::getInstance()->getConfig()->get("statadmin-stat-shown", "The stat '{stat}' of module '{module}' was shown!")));
				} else {
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stat}"], [$args[1] ?? "", $args[2] ?? ""], StatAPI::getInstance()->getConfig()->get("statadmin-stat-unknown", "The stat '{stat}' of module '{module}' doesn't exists!")));
				}
				return;
			case "setmodulename":
				if (count($args) > 2 and ($module = $this->getPlugin()->getModule($args[1], false)) instanceof Module) {
					$module->setDisplayName($args[2]);
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{displayName}"], [$module->getName(), $args[2]], StatAPI::getInstance()->getConfig()->get("statadmin-module-setname", "The display name of stat '{stat}' of module '{module}' was set to '{displayName}'!")));
				} else {
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}"], [$args[1] ?? ""], StatAPI::getInstance()->getConfig()->get("statadmin-module-unknown", "The module '{module}' doesn't exists!")));
				}
				return;
			case "setstatname":
				if (count($args) > 3 and ($stat = $this->getPlugin()->getStat($args[2], $this->getPlugin()->getModule($args[1]))) instanceof Stat) {
					$stat->setDisplayName($args[3]);
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stat}", "{displayName}"], [$stat->getModule()->getName(), $stat->getName(), $args[3]], StatAPI::getInstance()->getConfig()->get("statadmin-stat-setname", "The score of {player} in stat '{stat}' of module '{module}' was set to {score}!")));
				} else {
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stat}"], [$args[1] ?? "", $args[2] ?? ""], StatAPI::getInstance()->getConfig()->get("statadmin-stat-unknown", "The stat '{stat}' of module '{module}' doesn't exists!")));
				}
				return;
			case "resetstat":
				if (count($args) > 2 and ($stat = $this->getPlugin()->getStat($args[2], $this->getPlugin()->getModule($args[1]))) instanceof Stat) {
					$stat->resetData();
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stat}"], [$args[1] ?? "", $args[2] ?? ""], StatAPI::getInstance()->getConfig()->get("statadmin-stat-reseted", "The stat '{stat}' of module '{module}' was reset!")));
				} else {
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stat}"], [$args[1] ?? "", $args[2] ?? ""], StatAPI::getInstance()->getConfig()->get("statadmin-stat-unknown", "The stat '{stat}' of module '{module}' doesn't exists!")));
				}
				return;
			case "resetscores":
				if (count($args) > 1) {
					$this->getPlugin()->getDatabase()->executeChange(Query::REMOVE_PLAYER_DATA, ["player" => $args[1]], function (int $affectedRows) use ($sender, $args) {
						$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{player}"], [$args[1]], StatAPI::getInstance()->getConfig()->get("statadmin-stat-resetscore", "The scores of {player} were reset")));
					});
					$this->getPlugin()->reload();
				} else {
					$sender->sendMessage(StatAPI::getPrefix() . implode("§r\n", StatAPI::getInstance()->getConfig()->get("statadmin-help", ["§eList of all statadmin subcommands:", "§emodules§r - show a list of all modules", "§estats <module>§r - show stats of a module", "§edeletemodule <module>§r - delete a module", "§edeletestat <module> <stat>§r - deletes a stat", "§ehidemodule <module>§r - hide a module", "§eshowmodule <module>§r - show a module", "§ehidestat <module> <stat>§r - hide a stat", "§eshowstat <module> <stat>§r - show a stat", "§esetmodulename <module> <displayName>§r - set the display name of a module", "§esetstatname <module> <stat> <displayName>§r - set the display name of a stat", "§eresetstat <module> <stat>§r - resets the data of a stat", "§eresetscores <player>§r - resets all scores for the player", "§esetscore <module> <stat> <player> <score>§r - set the score of a player", "§erankstat <module> <stat> <position>§r - change the position of the stat, position has to be relative (1 -> 1 up, -1 -> 1 down)"])));
				}
				return;
			case "setscore":
				if (count($args) > 4 and ($stat = $this->getPlugin()->getStat($args[2], $this->getPlugin()->getModule($args[1]))) instanceof Stat) {
					try {
						$stat->setScore($args[3], $args[4]);
					} catch (InvalidArgumentException $e) {
						$sender->sendMessage(StatAPI::getPrefix() . implode("§r\n", StatAPI::getInstance()->getConfig()->get("statadmin-help", ["§eList of all statadmin subcommands:", "§emodules§r - show a list of all modules", "§estats <module>§r - show stats of a module", "§edeletemodule <module>§r - delete a module", "§edeletestat <module> <stat>§r - deletes a stat", "§ehidemodule <module>§r - hide a module", "§eshowmodule <module>§r - show a module", "§ehidestat <module> <stat>§r - hide a stat", "§eshowstat <module> <stat>§r - show a stat", "§esetmodulename <module> <displayName>§r - set the display name of a module", "§esetstatname <module> <stat> <displayName>§r - set the display name of a stat", "§eresetstat <module> <stat>§r - resets the data of a stat", "§eresetscores <player>§r - resets all scores for the player", "§esetscore <module> <stat> <player> <score>§r - set the score of a player", "§erankstat <module> <stat> <position>§r - change the position of the stat, position has to be relative (1 -> 1 up, -1 -> 1 down)"])));
					}
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stat}", "{player}", "{score}"], [$stat->getModule()->getName(), $stat->getName(), $args[3], $args[4]], StatAPI::getInstance()->getConfig()->get("statadmin-stat-setscore", "The score of {player} in stat '{stat}' of module '{module}' was set to {score}!")));
				} else {
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stat}"], [$args[1] ?? "", $args[2] ?? ""], StatAPI::getInstance()->getConfig()->get("statadmin-stat-unknown", "The stat '{stat}' of module '{module}' doesn't exists!")));
				}
				return;
			case "rankstat":
				if (count($args) > 3 and ($stat = $this->getPlugin()->getStat($args[2], $this->getPlugin()->getModule($args[1]))) instanceof Stat) {
					$currentPosition = max(0, 1 + array_search(strtolower($stat->getName()), array_keys($stat->getModule()->getStats())));
					$afterPosition = max(0, $currentPosition - $args[3]);
					if ($args[3] < 0) {
						$pos = $afterPosition;
						$this->getPlugin()->getDatabase()->executeChange(Query::SET_STAT_POSITION, ["stat" => $stat->getName(), "module" => $stat->getModule()->getName(), "position" => $pos]);
						foreach (array_slice($stat->getModule()->getStats(), $afterPosition) as $s) {
							$this->getPlugin()->getDatabase()->executeChange(Query::SET_STAT_POSITION, ["stat" => $s->getName(), "module" => $s->getModule()->getName(), "position" => ++$pos]);
						}
						$pos = $currentPosition;
						foreach (array_slice($stat->getModule()->getStats(), $currentPosition, $afterPosition - $currentPosition) as $s) {
							$this->getPlugin()->getDatabase()->executeChange(Query::SET_STAT_POSITION, ["stat" => $s->getName(), "module" => $s->getModule()->getName(), "position" => $pos++]);
						}
					} else {
						$pos = $afterPosition;
						foreach (array_slice($stat->getModule()->getStats(), $afterPosition - 1) as $s) {
							$this->getPlugin()->getDatabase()->executeChange(Query::SET_STAT_POSITION, ["stat" => $s->getName(), "module" => $s->getModule()->getName(), "position" => ++$pos]);
						}
						$this->getPlugin()->getDatabase()->executeChange(Query::SET_STAT_POSITION, ["stat" => $stat->getName(), "module" => $stat->getModule()->getName(), "position" => $afterPosition]);
					}
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stat}", "{position}"], [$stat->getModule()->getName(), $stat->getName(), $args[3]], StatAPI::getInstance()->getConfig()->get("statadmin-stat-ranked", "The position of stat '{stat}' of module '{module}' was changed by {position}")));
					$this->getPlugin()->reload();
				} else {
					$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stat}"], [$args[1] ?? "", $args[2] ?? ""], StatAPI::getInstance()->getConfig()->get("statadmin-stat-unknown", "The stat '{stat}' of module '{module}' doesn't exists!")));
				}
				return;
			case "help":
			default:
				$sender->sendMessage(StatAPI::getPrefix() . implode("§r\n", StatAPI::getInstance()->getConfig()->get("statadmin-help", ["§eList of all statadmin subcommands:", "§emodules§r - show a list of all modules", "§estats <module>§r - show stats of a module", "§edeletemodule <module>§r - delete a module", "§edeletestat <module> <stat>§r - deletes a stat", "§ehidemodule <module>§r - hide a module", "§eshowmodule <module>§r - show a module", "§ehidestat <module> <stat>§r - hide a stat", "§eshowstat <module> <stat>§r - show a stat", "§esetmodulename <module> <displayName>§r - set the display name of a module", "§esetstatname <module> <stat> <displayName>§r - set the display name of a stat", "§eresetstat <module> <stat>§r - resets the data of a stat", "§eresetscores <player>§r - resets all scores for the player", "§esetscore <module> <stat> <player> <score>§r - set the score of a player", "§erankstat <module> <stat> <position>§r - change the position of the stat, position has to be relative (1 -> 1 up, -1 -> 1 down)"])));
				return;
		}
	}
}
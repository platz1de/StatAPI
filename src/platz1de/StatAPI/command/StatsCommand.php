<?php

namespace platz1de\StatAPI\command;

use platz1de\StatAPI\Stat;
use pocketmine\command\CommandSender;
use platz1de\StatAPI\Module;
use platz1de\StatAPI\StatAPI;

class StatsCommand extends CommandBase
{
	/**
	 * StatsCommand constructor.
	 */
	public function __construct()
	{
		parent::__construct("stats");
		$this->setPermission("statapi.command.stats");
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param array         $args
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): void
	{
		if (!$this->testPermission($sender)) {
			return;
		}

		$player = $sender->getName();
		$module = $this->getOwningPlugin()->getDefaultModule();
		if (count($args) > 0) {
			if (($m = $this->getOwningPlugin()->getModule($args[0], false)) instanceof Module) {
				$module = $m;
				if (count($args) > 1) {
					$player = $args[1];
				}
			} else {
				$player = $args[0];
				if ((count($args) > 1) && ($m = $this->getOwningPlugin()->getModule($args[1], false)) instanceof Module) {
					$module = $m;
				}
			}
		}

		if (!$module->isVisible() && !$sender->hasPermission("statapi.seeall")) {
			$module = $this->getOwningPlugin()->getDefaultModule();
		}

		$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{player}"], [$module->getDisplayName(), $player], StatAPI::getInstance()->getConfig()->get("stats-header", "§e{player}'s stats in {module}:")));

		foreach ($module->getStats() as $stat) {
			if ($stat->isVisible() || $sender->hasPermission("statapi.seeall")) {
				$position = array_search(strtolower($player), array_keys($stat->getData()), true);
				if ($position === false) {
					$position = count($stat->getData());
				}
				if($stat->getType() === Stat::TYPE_UNKNOWN){
					$sender->sendMessage(str_replace(["{stat}", "{value}"], [$stat->getDisplayName(), $stat->getFormatedScore($player), ++$position], StatAPI::getInstance()->getConfig()->get("stats-list-no-position", "§e{stat}: §r{value}")));
				}else{
					$sender->sendMessage(str_replace(["{stat}", "{value}", "{position}"], [$stat->getDisplayName(), $stat->getFormatedScore($player), ++$position], StatAPI::getInstance()->getConfig()->get("stats-list", "§e{stat}: §r{value} §7(#{position})")));
				}
			}
		}
	}
}
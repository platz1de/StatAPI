<?php

namespace platz1de\StatAPI\command;

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
	 * @return mixed|void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return;
		}

		$player = $sender->getName();
		$module = $this->getPlugin()->getDefaultModule();
		if (count($args) > 0) {
			if (($m = $this->getPlugin()->getModule($args[0], false)) instanceof Module) {
				$module = $m;
				if (count($args) > 1) {
					$player = $args[1];
				}
			} else {
				$player = $args[0];
				if (count($args) > 1) {
					if (($m = $this->getPlugin()->getModule($args[1], false)) instanceof Module) {
						$module = $m;
					}
				}
			}
		}

		if (!$module->isVisible() and !$sender->hasPermission("statpi.seeall")) {
			$module = $this->getPlugin()->getDefaultModule();
		}

		$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{player}"], [$module->getDisplayName(), $player], StatAPI::getInstance()->getConfig()->get("stats-header", "§e{player}'s stats in {module}:")));

		foreach ($module->getStats() as $stat) {
			if ($stat->isVisible() or $sender->hasPermission("statpi.seeall")) {
				$position = array_search(strtolower($player), array_keys($stat->getData()));
				if($position === false){
					$position = count($stat->getData());
				}
				$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{stat}", "{value}", "{position}"], [$stat->getDisplayName(), $stat->getFormatedScore($player), ++$position], StatAPI::getInstance()->getConfig()->get("stats-list", "§e{stat}: §r{value} §7(#{position})")));
			}
		}
	}
}
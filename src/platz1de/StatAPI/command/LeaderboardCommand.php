<?php


namespace platz1de\StatAPI\command;


use pocketmine\command\CommandSender;
use platz1de\StatAPI\Module;
use platz1de\StatAPI\Stat;
use platz1de\StatAPI\StatAPI;

class LeaderboardCommand extends CommandBase
{
	/**
	 * LeaderboardCommand constructor.
	 */
	public function __construct()
	{
		parent::__construct("leaderboard");
		$this->setPermission("statapi.command.leaderboard");
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

		$stat = null;
		$module = $this->getOwningPlugin()->getDefaultModule();
		$page = 1;
		if (count($args) > 0) {
			//get module first
			for ($a = 0; $a < min(3, count($args)); $a++) {
				if (($m = $this->getOwningPlugin()->getModule($args[$a], false)) instanceof Module) {
					$module = $m;
					break;
				}
			}

			for ($b = 0; $b < min(3, count($args)); $b++) {
				if ($b !== $a) {
					if (($s = $this->getOwningPlugin()->getStat($args[$b], $module, false)) instanceof Stat) {
						$stat = $s;
					} elseif (is_numeric($args[$b])) {
						$page = $args[$b];
					}
				}
			}
		}

		if ($stat === null || $stat->getType() === Stat::TYPE_UNKNOWN || ((!$stat->isVisible() || !$module->isVisible()) && !$sender->hasPermission("statapi.seeall"))) {
			$sender->sendMessage($this->getUsage());
			return;
		}

		$data = array_keys($stat->getData());

		$pages = ceil(count($data) / 10);
		$page = min(max($page, 1), $pages);

		$sender->sendMessage(StatAPI::getPrefix() . str_replace(["{module}", "{stat}", "{page}", "{pages}"], [$module->getDisplayName(), $stat->getDisplayName(), $page, $pages], StatAPI::getInstance()->getConfig()->get("leaderboard-header", "§eLeaderboard of {module} - {stat}: §7[Page {page}/{pages}]")));

		$position = ($page - 1) * 10;
		foreach (array_slice($data, (min(ceil(count($data) / 10), max(1, $page)) - 1) * 10, 10) as $player) {
			$sender->sendMessage(str_replace(["{position}", "{player}", "{score}"], [++$position, $player, $stat->getFormatedScore($player)], StatAPI::getInstance()->getConfig()->get("leaderboard-list", "§1#{position} §r- §e{player}§r: {score}")));
		}
	}
}
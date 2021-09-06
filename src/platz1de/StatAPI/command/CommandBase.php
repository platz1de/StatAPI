<?php

namespace platz1de\StatAPI\command;

use pocketmine\command\Command;
use pocketmine\plugin\Plugin;
use platz1de\StatAPI\StatAPI;
use pocketmine\plugin\PluginOwned;

abstract class CommandBase extends Command implements PluginOwned
{
	/**
	 * CommandBase constructor.
	 * @param string $name
	 */
	public function __construct(string $name)
	{
		parent::__construct($name, StatAPI::getInstance()->getConfig()->get("command-" . $name . "-description", ""), StatAPI::getInstance()->getConfig()->get("command-" . $name . "-usage", null), StatAPI::getInstance()->getConfig()->get("command-" . $name . "-aliases", []));
	}

	/**
	 * @return StatAPI
	 */
	public function getOwningPlugin(): Plugin
	{
		return StatAPI::getInstance();
	}
}
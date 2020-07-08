<?php

namespace platz1de\StatAPI\command;

use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use platz1de\StatAPI\StatAPI;

abstract class CommandBase extends Command implements PluginIdentifiableCommand
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
	public function getPlugin(): Plugin
	{
		return StatAPI::getInstance();
	}
}
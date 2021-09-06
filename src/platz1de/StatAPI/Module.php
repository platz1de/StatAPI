<?php

namespace platz1de\StatAPI;

class Module
{
	/**
	 * Creating Modules isn't async!
	 *
	 * @param string $name
	 * @return Module
	 */
	public static function get(string $name): Module
	{
		if (($module = StatAPI::getInstance()->getModule($name)) instanceof self) {
			return $module;
		}

		StatAPI::getInstance()->getDatabase()->executeInsert(Query::REGISTER_MODULE, ["module" => $name], function (int $insertId, int $affectedRows) use ($name): void {
			StatAPI::getInstance()->registerModule(new Module($name));
		});
		StatAPI::getInstance()->getDatabase()->waitAll();
		return StatAPI::getInstance()->getModule($name);
	}

	private string $name;
	private string $displayName;
	private bool $visible;
	/**
	 * @var Stat[]
	 */
	private array $stats = [];

	/**
	 * Module constructor.
	 * @param string $name
	 * @param string $displayName
	 * @param bool   $visible
	 */
	public function __construct(string $name, string $displayName = "", bool $visible = true)
	{
		$this->name = $name;
		$this->displayName = $displayName;
		$this->visible = $visible;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getDisplayName(): string
	{
		return $this->displayName === "" ? $this->name : $this->displayName;
	}

	/**
	 * @param string $displayName
	 * @param bool   $save internal usage, better don't use this yourself
	 */
	public function setDisplayName(string $displayName, bool $save = true): void
	{
		if ($save) {
			StatAPI::getInstance()->getDatabase()->executeChange(Query::SET_MODULE_DISPLAYNAME, ["module" => $this->getName(), "displayName" => $displayName]);
		}
		$this->displayName = $displayName;
	}

	/**
	 * @return bool
	 */
	public function isVisible(): bool
	{
		return $this->visible;
	}

	/**
	 * @param bool $visible
	 * @param bool $save internal usage, better don't use this yourself
	 */
	public function setVisible(bool $visible, bool $save = true): void
	{
		if ($save) {
			StatAPI::getInstance()->getDatabase()->executeChange(Query::SET_MODULE_VISIBILITY, ["module" => $this->getName(), "visible" => $visible]);
		}
		$this->visible = $visible;
	}

	/**
	 * @return array
	 */
	public function getStats(): array
	{
		return $this->stats;
	}

	/**
	 * @param Stat $stat
	 * @internal
	 */
	public function addStat(Stat $stat): void
	{
		$this->stats[strtolower($stat->getName())] = $stat;
	}

	/**
	 * @param Stat $stat
	 */
	public function removeStat(Stat $stat): void
	{
		if (isset($this->stats[strtolower($stat->getName())])) {
			unset($this->stats[strtolower($stat->getName())]);
		}
	}

	public function __toString()
	{
		return $this->getName();
	}
}
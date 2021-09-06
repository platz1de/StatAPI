<?php

namespace platz1de\StatAPI;

use InvalidArgumentException;

class Stat
{
	/**
	 * Creating Stats isn't async!
	 *
	 * @param string $name
	 * @param Module $module
	 * @return Stat
	 */
	public static function get(string $name, Module $module): Stat
	{
		if (($stat = StatAPI::getInstance()->getStat($name, $module)) instanceof self) {
			return $stat;
		}

		StatAPI::getInstance()->getDatabase()->executeInsert(Query::REGISTER_STAT, ["stat" => $name, "module" => $module->getName()], function (int $insertId, int $affectedRows) use ($name, $module): void {
			$module->addStat(new Stat($name, $module));
		});
		StatAPI::getInstance()->getDatabase()->waitAll();
		return StatAPI::getInstance()->getStat($name, $module);
	}

	public const TYPE_UNKNOWN = 0; //sets the score; no leaderboard available
	public const TYPE_INCREASE = 1; //adds score to current score; highest score shown on top of the leaderboard
	public const TYPE_DECREASE = 2; //subtracts score from current score; lowest score shown on top of the leaderboard
	public const TYPE_HIGHEST = 3; //highest score saved; highest score shown on top of the leaderboard
	public const TYPE_LOWEST = 4; //lowest score saved; lowest score shown on top of the leaderboard

	//These don't have real values
	public const TYPE_RATIO = 10; //divides one stat by another stat; stats saved in default value and split by '//'; stats have to be from the same module; for example 'kills//deaths'; ignores displaytype

	public const DISPLAY_RAW = 0; //shows the score
	public const DISPLAY_LARGE = 1; //converts the score into a large number Format (eg. 8M, 21k...)
	public const DISPLAY_DATE = 2; //converts the score into a date; in seconds, see time()
	public const DISPLAY_DURATION = 3; //converts the score into a duration; in seconds
	public const DISPLAY_DURATION_MICRO = 4; //converts the score into a duration; in microseconds
	public const DISPLAY_DURATION_MINUTES = 5; //converts the score into a duration; in minutes

	/**
	 * @var string[]
	 */
	private array $data = [];

	/**
	 * Module constructor.
	 * @param string $name
	 * @param Module $module
	 * @param int    $type
	 * @param int    $displayType
	 * @param string $default
	 * @param string $displayName
	 * @param bool   $visible
	 */
	public function __construct(
		private string $name,
		private Module $module,
		private int    $type = self::TYPE_UNKNOWN,
		private int    $displayType = self::DISPLAY_RAW,
		private string $default = "0",
		private string $displayName = "",
		private bool   $visible = true)
	{
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return Module
	 */
	public function getModule(): Module
	{
		return $this->module;
	}

	/**
	 * @return int
	 */
	public function getType(): int
	{
		return $this->type;
	}

	/**
	 * @param int  $type
	 * @param bool $save internal usage, better don't use this yourself
	 */
	public function setType(int $type, bool $save = true): void
	{
		if ($save && $type !== $this->type) {
			StatAPI::getInstance()->getDatabase()->executeChange(Query::SET_STAT_TYPE, ["stat" => $this->getName(), "module" => $this->getModule()->getName(), "type" => $type]);
		}
		$this->type = $type;
	}

	/**
	 * @return int
	 */
	public function getDisplayType(): int
	{
		return $this->displayType;
	}

	/**
	 * @param int  $displayType
	 * @param bool $save internal usage, better don't use this yourself
	 */
	public function setDisplayType(int $displayType, bool $save = true): void
	{
		if ($save && $displayType !== $this->displayType) {
			StatAPI::getInstance()->getDatabase()->executeChange(Query::SET_STAT_DISPLAYTYPE, ["stat" => $this->getName(), "module" => $this->getModule()->getName(), "displayType" => $displayType]);
		}
		$this->displayType = $displayType;
	}

	/**
	 * @return string
	 */
	public function getDefault(): string
	{
		return $this->default;
	}

	/**
	 * @param string $default
	 * @param bool   $save internal usage, better don't use this yourself
	 */
	public function setDefault(string $default, bool $save = true): void
	{
		if ($save && $default !== $this->default) {
			StatAPI::getInstance()->getDatabase()->executeChange(Query::SET_STAT_DEFAULT, ["stat" => $this->getName(), "module" => $this->getModule()->getName(), "default" => $default]);
		}
		$this->default = $default;
	}

	/**
	 * @return string
	 */
	public function getDisplayName(): string
	{
		return empty($this->displayName) ? $this->getName() : $this->displayName;
	}

	/**
	 * @param string $displayName
	 * @param bool   $save internal usage, better don't use this yourself
	 */
	public function setDisplayName(string $displayName, bool $save = true): void
	{
		if ($save && $displayName !== $this->displayName) {
			StatAPI::getInstance()->getDatabase()->executeChange(Query::SET_STAT_DISPLAYNAME, ["stat" => $this->getName(), "module" => $this->getModule()->getName(), "displayName" => $displayName]);
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
		if ($save && $visible !== $this->visible) {
			StatAPI::getInstance()->getDatabase()->executeChange(Query::SET_STAT_VISIBILITY, ["stat" => $this->getName(), "module" => $this->getModule()->getName(), "visible" => $visible]);
		}
		$this->visible = $visible;
	}

	/**
	 * Returns an array with all known scores associated with their owner, playernames are lowercase
	 * @return array
	 * @see getScore for getting Score of a Player
	 * @see getFormatedScore for getting formatted Score of a Player
	 */
	public function getData(): array
	{
		return $this->data;
	}

	/**
	 * @param string $player
	 * @return string
	 */
	public function getScore(string $player): string
	{
		if ($this->getType() === self::TYPE_RATIO) {
			return $this->data[strtolower($player)] ?? "0"; //default is used for saving the used stats
		}

		return $this->data[strtolower($player)] ?? $this->default;
	}

	/**
	 * @param string $player
	 * @return string
	 */
	public function getFormatedScore(string $player): string
	{
		$score = $this->getScore($player);

		if (!is_numeric($score) || $this->getType() === self::TYPE_RATIO) {
			return $score;
		}

		switch ($this->displayType) {
			case self::DISPLAY_LARGE:
				$size = ceil(strlen($score) / 3) - 1;
				$number = substr($n = number_format($score), 0, strpos($n, ",") < 3 ? 4 : 3);
				$sizes = StatAPI::getInstance()->getConfig()->get("large-format", ["", "K", "M", "B", "T", "Qa", "Qi", "Sx", "Sp", "Oc", "No", "Dc", "Td"]);
				return $number . $sizes[$size];
			case self::DISPLAY_DATE:
				return date(StatAPI::getInstance()->getConfig()->get("date-format", "d M Y, H:i"), $score);
			case self::DISPLAY_DURATION_MINUTES:
				$hours = floor($score / 60);
				$minutes = sprintf("%02d", $score % 60);
				return str_replace(["{hours}", "{minutes}"], [$hours, $minutes], StatAPI::getInstance()->getConfig()->get("duration-minutes-format", "{hours}h {minutes}min"));
			case self::DISPLAY_DURATION:
				$minutes = floor($score / 60);
				$seconds = sprintf("%02d", $score % 60);
				return str_replace(["{minutes}", "{seconds}"], [$minutes, $seconds], StatAPI::getInstance()->getConfig()->get("duration-format", "{minutes}min {seconds}s"));
			case self::DISPLAY_DURATION_MICRO:
				$minutes = floor(($score % (1000 * 60 * 60)) / (1000 * 60));
				$seconds = sprintf("%02d", floor(($score % (1000 * 60)) / 1000));
				$microseconds = sprintf("%03d", $score % 1000);
				return str_replace(["{minutes}", "{seconds}", "{microseconds}"], [$minutes, $seconds, $microseconds], StatAPI::getInstance()->getConfig()->get("duration-micro-format", "{minutes}:{seconds}:{microseconds}"));
			case self::DISPLAY_RAW:
			default:
				return $score;
		}
	}


	/**
	 * Change score according to type
	 * @param string $player
	 * @param string $score
	 * @see setScore for setting score
	 */
	public function changeScore(string $player, string $score): void
	{
		if ($this->type !== self::TYPE_UNKNOWN && !is_numeric($score)) {
			throw new InvalidArgumentException("Non-numerical score for numerical Stat given");
		}

		if ($this->getType() === self::TYPE_RATIO) {
			//we don't have to save values for these
			return;
		}

		switch ($this->getType()) {
			case self::TYPE_UNKNOWN:
				$this->setScore($player, $score);
				return;
			case self::TYPE_INCREASE:
				StatAPI::getInstance()->getDatabase()->executeChange(Query::INCREASE_SCORE, ["player" => $player, "stat" => $this->getName(), "module" => $this->getModule()->getName(), "score" => $score]);
				return;
			case self::TYPE_DECREASE:
				StatAPI::getInstance()->getDatabase()->executeChange(Query::DECREASE_SCORE, ["player" => $player, "stat" => $this->getName(), "module" => $this->getModule()->getName(), "score" => $score]);
				return;
			case self::TYPE_HIGHEST:
				StatAPI::getInstance()->getDatabase()->executeChange(Query::HIGHER_SCORE, ["player" => $player, "stat" => $this->getName(), "module" => $this->getModule()->getName(), "score" => $score]);
				return;
			case self::TYPE_LOWEST:
				StatAPI::getInstance()->getDatabase()->executeChange(Query::LOWER_SCORE, ["player" => $player, "stat" => $this->getName(), "module" => $this->getModule()->getName(), "score" => $score]);
				return;
		}
	}

	/**
	 * Sets score
	 * Ignores type
	 * @param string $player
	 * @param string $score
	 * @param bool   $save internal usage, better don't use this yourself
	 * @see changeScore for changing the Score
	 */
	public function setScore(string $player, string $score, bool $save = true): void
	{
		if ($this->type !== self::TYPE_UNKNOWN && !is_numeric($score)) {
			throw new InvalidArgumentException("Non-numerical score for numerical Stat given");
		}

		if ($save && $this->getDisplayType() !== self::TYPE_RATIO) { //we don't have to save values for these
			StatAPI::getInstance()->getDatabase()->executeChange(Query::SET_SCORE, ["player" => $player, "stat" => $this->getName(), "module" => $this->getModule()->getName(), "score" => $score]);
		}

		$this->data[strtolower($player)] = $score;
	}

	/**
	 * @param string $player
	 * @param bool   $save internal usage, better don't use this yourself
	 */
	public function resetScore(string $player, bool $save = true): void
	{
		if ($save) {
			StatAPI::getInstance()->getDatabase()->executeChange(Query::REMOVE_SCORE, ["stat" => $this->getName(), "module" => $this->getModule()->getName(), "player" => $player]);
		}
		if (isset($this->data[$player])) {
			unset($this->data[$player]);
		}
	}

	/**
	 * @param bool $save internal usage, better don't use this yourself
	 */
	public function resetData(bool $save = true): void
	{
		if ($save) {
			StatAPI::getInstance()->getDatabase()->executeChange(Query::REMOVE_STAT_DATA, ["stat" => $this->getName(), "module" => $this->getModule()->getName()]);
		}
		$this->data = [];
	}

	public function sort(): void
	{
		switch ($this->getType()) {
			case self::TYPE_RATIO: //TODO: Here could lowest be best
			case self::TYPE_INCREASE:
			case self::TYPE_HIGHEST:
				arsort($this->data);
				break;
			case self::TYPE_DECREASE:
			case self::TYPE_LOWEST:
				asort($this->data);
				break;
		}
	}

	public function __toString()
	{
		return $this->getName();
	}
}
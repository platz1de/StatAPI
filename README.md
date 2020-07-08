#StatAPI

A simple API to manage stats on your server network using [libasynql](https://github.com/poggit/libasynql)<br>
MySQL Database required

###Disclaimer
**This is just an API, a plugin for basic stats may be following**

Changes will take a bit to be synced on the whole network

##Features

* see and change the stats of every player
* create unlimited modules to hold your stats, normally you want to have one module for every gamemode
* Leaderboards (ascending and descending)
* different stat types for simpler handling
* different display types (e.g. for dates or durations)
* hide stats or modules for normal players
* sync your stats on different servers

##Commands

Aliases, Usage and Descriptions can be changed in the config

Command | Usage | Description
--------|-------|------------
stats|/stats [module/player] [player/module]|See the stats of a player that are in the module
leaderboard|/leaderboard [module] <stat> [page]|See the leaderboard of a stat
statadmin|/statadmin <subcommand> [args]|Manage your stats manually

##Usage
get a module (or create it if it doesn't exist)
```php
$module = \platz1de\StatAPI\Module::get("myEpicModule");
```

get a stat (or create it if it doesn't exist)
```php
/** @var $module \platz1de\StatAPI\Module */
$stat = \platz1de\StatAPI\Stat::get("myEpicStat", $module);
```

set a stat type (default: TYPE_UNKNOWN)

Type | Description
-----|------------
TYPE_UNKNOWN | sets the score; no leaderboard available
TYPE_INCREASE | adds score to current score; highest score shown on top of the leaderboard
TYPE_DECREASE | subtracts score from current score; lowest score shown on top of the leaderboard
TYPE_HIGHEST | highest score saved; highest score shown on top of the leaderboard
TYPE_LOWEST | lowest score saved; lowest score shown on top of the leaderboard

```php
/** @var $stat \platz1de\StatAPI\Stat */
$stat->setType(\platz1de\StatAPI\Stat::TYPE_INCREASE);
```

change the score of a player (in the example by 123)
```php
/** @var $stat \platz1de\StatAPI\Stat */
$stat->changeScore("somePlayer", 123);
```

set the score of a player (in the example to 321)
```php
/** @var $stat \platz1de\StatAPI\Stat */
$stat->setScore("somePlayer", 321);
```

get the score of a player
```php
/** @var $stat \platz1de\StatAPI\Stat */
$stat->getScore("somePlayer");
```

###Advanced Usage

set a display type (for most stats you don't need this step; default: DISPLAY_RAW)

Type | Description
-----|------------
DISPLAY_RAW | just shows the score
DISPLAY_LARGE | converts the score into a large number Format (eg. 8M, 21k...)
DISPLAY_DATE | converts the score into a date; in seconds, see time()
DISPLAY_DURATION | converts the score into a duration; score given in seconds
DISPLAY_DURATION_MICRO | converts the score into a duration; score given in microseconds
DISPLAY_DURATION_MINUTES | converts the score into a duration; score given in minutes

```php
/** @var $stat \platz1de\StatAPI\Stat */
$stat->setDisplayType(\platz1de\StatAPI\Stat::DISPLAY_LARGE);
```

set the default value of the stat
```php
/** @var $thing \platz1de\StatAPI\Stat */
$thing->setDefault(25);
```

hide a stat or module from normal players (you could also use the /statsadmin command)
```php
/** @var $thing \platz1de\StatAPI\Stat|\platz1de\StatAPI\Module */
$thing->setVisible(false);
```

set a display name for a stat or module (you could also use the /statsadmin command)
```php
/** @var $thing \platz1de\StatAPI\Stat|\platz1de\StatAPI\Module */
$thing->setDisplayName("My Epic Stat");
```

get the formatted score of a player (ready to send as a message)
```php
/** @var $stat \platz1de\StatAPI\Stat */
$stat->getFormatedScore("somePlayer");
```


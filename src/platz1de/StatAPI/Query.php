<?php

namespace platz1de\StatAPI;

class Query
{
	public const INIT_MODULES_TABLE = "statapi.init.modules"; //[]
	public const INIT_STATS_TABLE = "statapi.init.stats"; //[]
	public const INIT_DATA_TABLE = "statapi.init.data"; //[]

	public const REGISTER_MODULE = "statapi.register.module"; //[module]
	public const REGISTER_STAT = "statapi.register.stat"; //[stat, module]

	public const UNREGISTER_MODULE = "statapi.unregister.module"; //[module]
	public const UNREGISTER_STAT = "statapi.unregister.stat"; //[stat]

	public const SET_MODULE_DISPLAYNAME = "statapi.set.module.displayName"; //[module, displayName]
	public const SET_MODULE_VISIBILITY = "statapi.set.module.visible"; //[module, visible]

	public const SET_STAT_TYPE = "statapi.set.stat.type"; //[stat, module, type]
	public const SET_STAT_DISPLAYTYPE = "statapi.set.stat.displayType"; //[stat, module, displayType]
	public const SET_STAT_DEFAULT = "statapi.set.stat.default"; //[stat, module, default]
	public const SET_STAT_DISPLAYNAME = "statapi.set.stat.displayName"; //[stat, module, displayName]
	public const SET_STAT_VISIBILITY = "statapi.set.stat.visible"; //[stat, module, visible]
	public const SET_STAT_POSITION = "statapi.set.stat.position"; //[stat, module, position]

	public const SET_SCORE = "statapi.set.data.score.set"; //[player, stat, module, score]
	public const INCREASE_SCORE = "statapi.set.data.score.increase"; //[player, stat, module, score]
	public const DECREASE_SCORE = "statapi.set.data.score.decrease"; //[player, stat, module, score]
	public const HIGHER_SCORE = "statapi.set.data.score.highest"; //[player, stat, module, score]
	public const LOWER_SCORE = "statapi.set.data.score.lowest"; //[player, stat, module, score]

	public const GET_MODULE_DISPLAYNAME = "statapi.get.module.displayName"; //[module]
	public const GET_MODULE_VISIBILITY = "statapi.get.module.visible"; //[module]
	public const GET_MODULE = "statapi.get.module.data"; //[module]
	public const GET_MODULES = "statapi.get.module.all"; //[]

	public const GET_STAT_MODULE = "statapi.get.stat.module"; //[stat, module]
	public const GET_STAT_TYPE = "statapi.get.stat.type"; //[stat, module]
	public const GET_STAT_DISPLAYTYPE = "statapi.get.stat.displayType"; //[stat, module]
	public const GET_STAT_DEFAULT = "statapi.get.stat.default"; //[stat, module]
	public const GET_STAT_DISPLAYNAME = "statapi.get.stat.displayName"; //[stat, module]
	public const GET_STAT_VISIBILITY = "statapi.get.stat.visible"; //[stat, module]
	public const GET_STAT = "statapi.get.stat.data"; //[stat, module]
	public const GET_STATS = "statapi.get.stat.all"; //[]

	public const GET_SCORE = "statapi.get.data.score.of"; //[stat, module, player]
	public const GET_SCORES = "statapi.get.data.score.all"; //[stat, module]
	public const GET_DATA = "statapi.get.data.all"; //[]

	public const REMOVE_SCORE = "statapi.remove.data.score"; //[stat, module, player]
	public const REMOVE_PLAYER_DATA = "statapi.remove.data.player"; //[player]
	public const REMOVE_STAT_DATA = "statapi.remove.data.stat"; //[stat, module]
}
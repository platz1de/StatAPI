<?php

namespace platz1de\StatAPI;

class Query
{
	const INIT_MODULES_TABLE = "statapi.init.modules"; //[]
	const INIT_STATS_TABLE = "statapi.init.stats"; //[]
	const INIT_DATA_TABLE = "statapi.init.data"; //[]

	const REGISTER_MODULE = "statapi.register.module"; //[module]
	const REGISTER_STAT = "statapi.register.stat"; //[stat, module]

	const UNREGISTER_MODULE = "statapi.unregister.module"; //[module]
	const UNREGISTER_STAT = "statapi.unregister.stat"; //[stat]

	const SET_MODULE_DISPLAYNAME = "statapi.set.module.displayName"; //[module, displayName]
	const SET_MODULE_VISIBILITY = "statapi.set.module.visible"; //[module, visible]

	const SET_STAT_TYPE = "statapi.set.stat.type"; //[stat, module, type]
	const SET_STAT_DISPLAYTYPE = "statapi.set.stat.displayType"; //[stat, module, displayType]
	const SET_STAT_DEFAULT = "statapi.set.stat.default"; //[stat, module, default]
	const SET_STAT_DISPLAYNAME = "statapi.set.stat.displayName"; //[stat, module, displayName]
	const SET_STAT_VISIBILITY = "statapi.set.stat.visible"; //[stat, module, visible]
	const SET_STAT_POSITION = "statapi.set.stat.position"; //[stat, module, position]

	const SET_SCORE = "statapi.set.data.score.set"; //[player, stat, module, score]
	const INCREASE_SCORE = "statapi.set.data.score.increase"; //[player, stat, module, score]
	const DECREASE_SCORE = "statapi.set.data.score.decrease"; //[player, stat, module, score]
	const HIGHER_SCORE = "statapi.set.data.score.highest"; //[player, stat, module, score]
	const LOWER_SCORE = "statapi.set.data.score.lowest"; //[player, stat, module, score]

	const GET_MODULE_DISPLAYNAME = "statapi.get.module.displayName"; //[module]
	const GET_MODULE_VISIBILITY = "statapi.get.module.visible"; //[module]
	const GET_MODULE = "statapi.get.module.data"; //[module]
	const GET_MODULES = "statapi.get.module.all"; //[]

	const GET_STAT_MODULE = "statapi.get.stat.module"; //[stat, module]
	const GET_STAT_TYPE = "statapi.get.stat.type"; //[stat, module]
	const GET_STAT_DISPLAYTYPE = "statapi.get.stat.displayType"; //[stat, module]
	const GET_STAT_DEFAULT = "statapi.get.stat.default"; //[stat, module]
	const GET_STAT_DISPLAYNAME = "statapi.get.stat.displayName"; //[stat, module]
	const GET_STAT_VISIBILITY = "statapi.get.stat.visible"; //[stat, module]
	const GET_STAT = "statapi.get.stat.data"; //[stat, module]
	const GET_STATS = "statapi.get.stat.all"; //[]

	const GET_SCORE = "statapi.get.data.score.of"; //[stat, module, player]
	const GET_SCORES = "statapi.get.data.score.all"; //[stat, module]
	const GET_DATA = "statapi.get.data.all"; //[]

	const REMOVE_SCORE = "statapi.remove.data.score"; //[stat, module, player]
	const REMOVE_PLAYER_DATA = "statapi.remove.data.player"; //[player]
	const REMOVE_STAT_DATA = "statapi.remove.data.stat"; //[stat, module]
}
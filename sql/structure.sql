DROP TABLE IF EXISTS `buildings`;
CREATE TABLE IF NOT EXISTS `buildings` (
  `building_id` tinyint(3) unsigned NOT NULL auto_increment,
  `building_constant` varchar(100) NOT NULL default '',
  `building_name` varchar(100) NOT NULL default '',
  `building_icon` varchar(50) NOT NULL default '',
  `building_team` enum('alien','human','world') NOT NULL default 'world',
  `building_efficiency_multiplier` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`building_id`),
  UNIQUE KEY `building_constant` (`building_constant`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `weapons`;
CREATE TABLE IF NOT EXISTS `weapons` (
  `weapon_id` tinyint(3) unsigned NOT NULL auto_increment,
  `weapon_constant` varchar(100) NOT NULL default '',
  `weapon_name` varchar(100) NOT NULL default '',
  `weapon_icon` varchar(50) NOT NULL default '',
  `weapon_team` enum('alien','human','world') NOT NULL default 'world',
  PRIMARY KEY  (`weapon_id`),
  UNIQUE KEY `weapon_constant` (`weapon_constant`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `builds`;
CREATE TABLE IF NOT EXISTS `builds` (
  `build_id` int(11) unsigned NOT NULL auto_increment,
  `build_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `build_game_id` int(11) unsigned NOT NULL default '0',
  `build_gametime` time NOT NULL default '00:00:00',
  `build_player_id` int(11) unsigned NOT NULL default '0',
  `build_building_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`build_id`),
  KEY `build_player_id` (`build_player_id`),
  KEY `build_building_id` (`build_building_id`),
  KEY `build_game_id` (`build_game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `decons`;
CREATE TABLE IF NOT EXISTS `decons` (
  `decon_id` int(11) unsigned NOT NULL auto_increment,
  `decon_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `decon_game_id` int(11) unsigned NOT NULL default '0',
  `decon_gametime` time NOT NULL default '00:00:00',
  `decon_player_id` int(11) unsigned NOT NULL default '0',
  `decon_building_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`decon_id`),
  KEY `decon_player_id` (`decon_player_id`),
  KEY `decon_building_id` (`decon_building_id`),
  KEY `decon_game_id` (`decon_game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `destructions`;
CREATE TABLE IF NOT EXISTS `destructions` (
  `destruct_id` int(11) unsigned NOT NULL auto_increment,
  `destruct_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `destruct_game_id` int(11) unsigned NOT NULL default '0',
  `destruct_gametime` time NOT NULL default '00:00:00',
  `destruct_player_id` int(11) unsigned NOT NULL default '0',
  `destruct_building_id` tinyint(3) unsigned NOT NULL default '0',
  `destruct_weapon_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`destruct_id`),
  KEY `kill_source_player_id` (`destruct_player_id`,`destruct_building_id`),
  KEY `destruct_player_id` (`destruct_player_id`),
  KEY `destruct_weapon_id` (`destruct_weapon_id`),
  KEY `destruct_building_id` (`destruct_building_id`),
  KEY `destruct_game_id` (`destruct_game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kills`;
CREATE TABLE IF NOT EXISTS `kills` (
  `kill_id` int(11) unsigned NOT NULL auto_increment,
  `kill_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `kill_game_id` int(11) unsigned NOT NULL default '0',
  `kill_gametime` time NOT NULL default '00:00:00',
  `kill_type` enum('world','enemy','team') NOT NULL default 'world',
  `kill_source_player_id` int(11) unsigned NOT NULL default '0',
  `kill_target_player_id` int(11) unsigned NOT NULL default '0',
  `kill_weapon_id` tinyint(3) unsigned NOT NULL default '0',
  `kill_assist_player_id` int(11) unsigned default NULL,
  `kill_assist_type` enum('world','enemy','team') NOT NULL default 'world',
  PRIMARY KEY  (`kill_id`),
  KEY `kill_source_player_id` (`kill_source_player_id`),
  KEY `kill_target_player_id` (`kill_target_player_id`),
  KEY `kill_weapon_id` (`kill_weapon_id`),
  KEY `kill_type` (`kill_type`),
  KEY `kill_assist_player_id` (`kill_assist_player_id`),
  KEY `kill_assist_type` (`kill_assist_type`),
  KEY `kill_game_id` (`kill_game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `games`;
CREATE TABLE IF NOT EXISTS `games` (
  `game_id` int(10) unsigned NOT NULL auto_increment,
  `game_timestamp` timestamp NOT NULL default '0000-00-00 00:00:00',
  `game_map_id` int(11) unsigned NOT NULL default '0',
  `game_match_id` varchar(64) default NULL,
  `game_winner` enum('undefined','none','aliens','humans','tie','draw') NOT NULL default 'undefined',
  `game_length` time NOT NULL default '00:00:00',
  `game_alien_kills` int(10) unsigned NOT NULL default '0',
  `game_human_kills` int(10) unsigned NOT NULL default '0',
  `game_alien_deaths` int(10) unsigned NOT NULL default '0',
  `game_human_deaths` int(10) unsigned NOT NULL default '0',
  `game_total_kills` int(10) unsigned NOT NULL default '0',
  `game_total_deaths` int(10) unsigned NOT NULL default '0',
  `game_is_empty` boolean NOT NULL default TRUE,
  PRIMARY KEY  (`game_id`),
  KEY `game_map_id` (`game_map_id`),
  KEY `game_match_id` (`game_match_id`),
  KEY `game_winner` (`game_winner`),
  KEY `game_total_kills` (`game_total_kills`),
  KEY `game_total_deaths` (`game_total_deaths`),
  KEY `game_length` (`game_length`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `per_game_stats`;
CREATE TABLE IF NOT EXISTS `per_game_stats` (
  `stats_id` int(11) unsigned NOT NULL auto_increment,
  `stats_player_id` int(11) unsigned NOT NULL default '0',
  `stats_game_id` int(11) unsigned NOT NULL default '0',
  `stats_kills` int(11) unsigned NOT NULL default '0',
  `stats_teamkills` int(11) unsigned NOT NULL default '0',
  `stats_assists` int(11) unsigned NOT NULL default '0',
  `stats_enemyassists` int(11) unsigned NOT NULL default '0',
  `stats_deaths` int(11) unsigned NOT NULL default '0',
  `stats_score` int(11) NOT NULL default '0',
  `stats_time_alien` int(11) NOT NULL default '0',
  `stats_time_human` int(11) NOT NULL default '0',
  `stats_time_spec` int(11) NOT NULL default '0',
  PRIMARY KEY  (`stats_id`),
  KEY `stats_player_and_game` (`stats_player_id`,`stats_game_id`),
  KEY `stats_player_id` (`stats_player_id`),
  KEY `stats_game_id` (`stats_game_id`),
  KEY `stats_score` (`stats_score`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `maps`;
CREATE TABLE IF NOT EXISTS `maps` (
  `map_id` int(11) unsigned NOT NULL auto_increment,
  `map_name` varchar(100) NOT NULL default '',
  `map_longname` varchar(200) default NULL,
  `map_levelshot` longblob,
  PRIMARY KEY  (`map_id`),
  UNIQUE KEY `map_name` (`map_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `map_stats`;
CREATE TABLE IF NOT EXISTS `map_stats` (
  `mapstat_id` int(11) unsigned NOT NULL,
  `mapstat_games` int(11) unsigned NOT NULL default 0,
  `mapstat_time` int(11) unsigned NOT NULL default 0,
  `mapstat_alien_wins` int(11) unsigned NOT NULL default 0,
  `mapstat_human_wins` int(11) unsigned NOT NULL default 0,
  `mapstat_ties` int(11) unsigned NOT NULL default 0,
  `mapstat_draws` int(11) unsigned NOT NULL default 0,
  `mapstat_alien_kills` int(11) unsigned NOT NULL default 0,
  `mapstat_human_kills` int(11) unsigned NOT NULL default 0,
  `mapstat_alien_deaths` int(11) unsigned NOT NULL default 0,
  `mapstat_human_deaths` int(11) unsigned NOT NULL default 0,
  PRIMARY KEY (`mapstat_id`),
  KEY `mapstat_games` (`mapstat_games`),
  KEY `mapstat_time` (`mapstat_time`),
  KEY `mapstat_alien_wins` (`mapstat_alien_wins`),
  KEY `mapstat_human_wins` (`mapstat_human_wins`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `players`;
CREATE TABLE IF NOT EXISTS `players` (
  `player_id` int(11) unsigned NOT NULL auto_increment,
  `player_qkey` varchar(32) NOT NULL default 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
  `player_is_bot` boolean NOT NULL default FALSE,
  `player_name` varchar(128) NOT NULL default '',
  `player_name_uncolored` varchar(128) NOT NULL default '',
  `player_games_played` int(11) NOT NULL default '0',
  `player_first_game_id` int(11) unsigned NOT NULL default '0',
  `player_first_gametime` timestamp NOT NULL default '0000-00-00 00:00:00',
  `player_last_game_id` int(11) unsigned NOT NULL default '0',
  `player_last_gametime` timestamp NOT NULL default '0000-00-00 00:00:00',
  `player_game_time_factor` float(5,2) NOT NULL default '0.00',
  `player_kill_efficiency` float(5,2) NOT NULL default '0.00',
  `player_destruction_efficiency` float(5,2) NOT NULL default '0.00',
  `player_total_efficiency` float(5,2) NOT NULL default '0.00',
  `player_kills` int(11) NOT NULL default '0',
  `player_kills_alien` int(11) NOT NULL default '0',
  `player_kills_human` int(11) NOT NULL default '0',
  `player_teamkills` int(11) NOT NULL default '0',
  `player_teamkills_alien` int(11) NOT NULL default '0',
  `player_teamkills_human` int(11) NOT NULL default '0',
  `player_assists` int(11) NOT NULL default '0',
  `player_assists_alien` int(11) NOT NULL default '0',
  `player_assists_human` int(11) NOT NULL default '0',
  `player_enemyassists` int(11) NOT NULL default '0',
  `player_enemyassists_alien` int(11) NOT NULL default '0',
  `player_enemyassists_human` int(11) NOT NULL default '0',
  `player_deaths` int(11) NOT NULL default '0',
  `player_deaths_enemy` int(11) NOT NULL default '0',
  `player_deaths_enemy_alien` int(11) NOT NULL default '0',
  `player_deaths_enemy_human` int(11) NOT NULL default '0',
  `player_deaths_team_alien` int(11) NOT NULL default '0',
  `player_deaths_team_human` int(11) NOT NULL default '0',
  `player_deaths_world_alien` int(11) NOT NULL default '0',
  `player_deaths_world_human` int(11) NOT NULL default '0',
  `player_time_alien` int(11) NOT NULL default '0',
  `player_time_human` int(11) NOT NULL default '0',
  `player_time_spec` int(11) NOT NULL default '0',
  `player_score_total` int(11) NOT NULL default '0',
  PRIMARY KEY  (`player_id`),
  KEY `player_qkey` (`player_qkey`),
  KEY `player_name` (`player_name`(15)),
  FULLTEXT KEY `player_name_uncolored` (`player_name_uncolored`),
  KEY `player_score_total` (`player_score_total`),
  KEY `player_kills` (`player_kills`),
  KEY `player_assists` (`player_assists`),
  KEY `player_deaths_enemy` (`player_deaths_enemy`),
  KEY `player_teamkills` (`player_teamkills`),
  KEY `player_enemyassists` (`player_enemyassists`),
  KEY `player_games_played` (`player_games_played`),
  KEY `player_last_game_id` (`player_last_game_id`),
  KEY `player_game_time_factor` (`player_game_time_factor`),
  KEY `player_total_efficiency` (`player_total_efficiency`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `nicks`;
CREATE TABLE `nicks` (
  `nick_id` int(11) unsigned NOT NULL auto_increment,
  `nick_player_id` int(11) unsigned NOT NULL default '0',
  `nick_name_uncolored` varchar(128) NOT NULL default '',
  `nick_name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`nick_id`),
  KEY `nick_id_and_name` (`nick_player_id`, `nick_name_uncolored` ),
  KEY `nick_player_id` (`nick_player_id`),
  KEY `nick_name_uncolored` (`nick_name_uncolored`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `skill`;
CREATE TABLE `skill` (
  `skill_id` int(11) unsigned NOT NULL auto_increment,
  `skill_player_id` int(11) unsigned NOT NULL,
  `skill_game_id` int(11) unsigned NOT NULL default '0',
  `skill_mu` double precision NOT NULL,
  `skill_sigma` double precision NOT NULL,
  `skill_alien_mu` double precision NOT NULL,
  `skill_alien_sigma` double precision NOT NULL,
  `skill_human_mu` double precision NOT NULL,
  `skill_human_sigma` double precision NOT NULL,
  PRIMARY KEY  (`skill_id`),
  KEY `skill_player_game` (`skill_player_id`, `skill_game_id`),
  KEY `skill_game_player` (`skill_game_id`, `skill_player_id`),
  KEY `skill_mu` (`skill_mu`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP VIEW IF EXISTS `skill_last`;
CREATE VIEW `skill_last` AS
  SELECT t.skill_id, t.skill_player_id, t.skill_game_id, 
        t.skill_mu, t.skill_sigma,
        t.skill_alien_mu, t.skill_alien_sigma,
        t.skill_human_mu, t.skill_human_sigma
    FROM skill t
   WHERE t.skill_game_id IN
     ( SELECT MAX(s.skill_game_id) FROM skill s
        WHERE s.skill_player_id = t.skill_player_id )
;

DROP TABLE IF EXISTS `says`;
CREATE TABLE IF NOT EXISTS `says` (
  `say_id` int(11) unsigned NOT NULL auto_increment,
  `say_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `say_game_id` int(11) unsigned NOT NULL default '0',
  `say_gametime` time NOT NULL default '00:00:00',
  `say_mode` enum('public','alien','human','spectator') NOT NULL default 'public',
  `say_player_id` int(11) unsigned NOT NULL default '0',
  `say_message` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`say_id`),
  KEY `say_player_id` (`say_player_id`),
  KEY `say_game_id` (`say_game_id`),
  KEY `say_mode` (`say_mode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `votes`;
CREATE TABLE IF NOT EXISTS `votes` (
  `vote_id` int(11) unsigned NOT NULL auto_increment,
  `vote_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `vote_game_id` int(11) unsigned NOT NULL default '0',
  `vote_gametime` time NOT NULL default '00:00:00',
  `vote_player_id` int(11) unsigned NOT NULL default '0',
  `vote_victim_id` int(11) unsigned NOT NULL default '0',
  `vote_mode` enum('public','alien','human') NOT NULL default 'public',
  `vote_type` varchar(64) NOT NULL default '',
  `vote_arg` varchar(64) default NULL,
  `vote_pass` enum('yes','no') NOT NULL default 'no',
  `vote_yes` int(11) unsigned NOT NULL default '0',
  `vote_no` int(11) unsigned NOT NULL default '0',
  `vote_count` int(11) unsigned NOT NULL default '0',
  `vote_endtime` time default NULL,
  PRIMARY KEY (`vote_id`),
  KEY `vote_player_id` (`vote_player_id`),
  KEY `vote_victim_id` (`vote_victim_id`),
  KEY `vote_game_id` (`vote_game_id`),
  KEY `vote_type` (`vote_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `state`;
CREATE TABLE IF NOT EXISTS `state` (
  `log_id` int(11) unsigned NOT NULL default '0',
  `log_filesize` int(11) unsigned NOT NULL default '0',
  `log_offset` int(11) unsigned NOT NULL default '0',
  `log_runcount` int(11) unsigned NOT NULL default '0',
  `log_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP VIEW IF EXISTS `human_players`;
CREATE VIEW `human_players` AS SELECT `player_id` FROM `players` WHERE `player_is_bot` = FALSE;

DROP VIEW IF EXISTS `played_games`;
CREATE VIEW `played_games` AS
  SELECT `per_game_stats`.`stats_game_id` AS `game_id`
    FROM `per_game_stats` LEFT JOIN `human_players` ON `stats_player_id` = `player_id`
    WHERE (`per_game_stats`.`stats_time_human` > 0 OR `per_game_stats`.`stats_time_alien` > 0)
    GROUP BY `stats_game_id`;

DROP VIEW IF EXISTS `say_games`;
CREATE VIEW `say_games` AS SELECT `say_game_id` AS `game_id` FROM `says` GROUP BY `say_game_id`;

DROP VIEW IF EXISTS `active_games`;
CREATE VIEW `active_games` AS SELECT * FROM `played_games` UNION SELECT * FROM `say_games`;

DROP VIEW IF EXISTS `completed_games`;
CREATE VIEW `completed_games` AS SELECT * FROM `games` WHERE `game_winner` != 'none' AND `game_winner` != 'undefined';

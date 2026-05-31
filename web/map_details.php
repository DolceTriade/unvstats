<?php
/**
 * Project:     Unvstats
 * File:        map_details.php
 *
 * For license and version information, see /index.php
 */

require_once 'core/init.inc.php';

if (!isset($_GET['map_id'])) {
  die('No map id given');
}

// Player details
$map_details = $db->GetRow("SELECT map_id,
                                   map_name,
                                   if (map_longname != '', map_longname, map_name) AS map_text_name,
                                   mapstat_games,
                                   SEC_TO_TIME( mapstat_time ) AS mapstat_text_time,
                                   mapstat_alien_wins,
                                   mapstat_human_wins,
                                   mapstat_ties,
                                   mapstat_draws,
                                   mapstat_alien_kills,
                                   mapstat_human_kills,
                                   mapstat_alien_deaths,
                                   mapstat_human_deaths
                               FROM map_stats
                               INNER JOIN maps ON map_id = mapstat_id
                               WHERE map_id = ?
                               LIMIT 0, 1",
                               array($_GET['map_id']));

if( !isset($map_details['map_id']) ):
  die ("map id not found");
endif;


// Prefered weapons
$weapon_kills = $db->GetAll("SELECT COUNT(kill_id) AS weapon_count,
                                        weapon_name,
                                        weapon_icon
                                 FROM kills
                                 INNER JOIN weapons ON weapon_id = kill_weapon_id
                                 INNER JOIN games ON game_id = kill_game_id
                                 INNER JOIN players AS victim ON kill_target_player_id = victim.player_id
                                 LEFT JOIN players AS source ON kill_source_player_id = source.player_id
                                 LEFT JOIN players AS assist ON kill_assist_player_id = assist.player_id
                                 WHERE game_map_id = ?
                                       AND ".game_nonempty_filter_sql('games')."
                                       AND kill_type != 'team'
                                       AND ".(session_include_bots()
                                         ? "1 = 1"
                                         : "victim.player_is_bot = FALSE
                                            AND IFNULL(source.player_is_bot, FALSE) = FALSE
                                            AND IFNULL(assist.player_is_bot, FALSE) = FALSE")."
                                 GROUP BY kill_weapon_id
                                 ORDER BY weapon_count DESC, weapon_name ASC",
                                 array($map_details['map_id']));

// Destroyed structures
$destroyed_structures = $db->GetAll("SELECT COUNT(destruct_id) AS building_count,
                                            building_name,
                                            building_icon
                                     FROM destructions
                                     INNER JOIN games ON game_id = destruct_game_id
                                     INNER JOIN players ON destruct_player_id = player_id
                                     INNER JOIN buildings ON building_id = destruct_building_id
                                     INNER JOIN weapons ON weapon_id = destruct_weapon_id
                                     WHERE game_map_id = ?
                                           AND ".game_nonempty_filter_sql('games')."
                                           AND ".player_bot_filter_sql('players')."
                                           AND building_team != weapon_team
                                           AND weapon_constant != 'MOD_NOCREEP'
                                     GROUP BY destruct_building_id
                                     ORDER BY building_count DESC, building_name ASC",
                                     array($map_details['map_id']));

$built_structures = $db->GetAll("SELECT COUNT(build_id) AS building_count,
                                            building_name,
                                            building_icon
                                     FROM builds
                                     INNER JOIN players ON build_player_id = player_id
                                     INNER JOIN buildings ON building_id = build_building_id
                                     INNER JOIN games ON game_id = build_game_id
                                     WHERE game_map_id = ?
                                           AND ".game_nonempty_filter_sql('games')."
                                           AND ".player_bot_filter_sql('players')."
                                     GROUP BY build_building_id
                                     ORDER BY building_count DESC, building_name ASC",
                                     array($map_details['map_id']));

$votes_called = $db->GetAll("SELECT COUNT(vote_id) AS vote_count,
                                            vote_type
                                     FROM votes
                                     INNER JOIN games ON game_id = vote_game_id
                                     INNER JOIN players ON vote_player_id = player_id
                                     WHERE game_map_id = ?
                                           AND ".game_nonempty_filter_sql('games')."
                                           AND ".player_bot_filter_sql('players')."
                                     GROUP BY vote_type
                                     ORDER BY vote_count DESC, vote_type ASC",
                                     array($map_details['map_id']));

// Assign variables to template
$tpl->assign('map_details',          $map_details);
$tpl->assign('weapon_kills',         $weapon_kills);
$tpl->assign('destroyed_structures', $destroyed_structures);
$tpl->assign('built_structures',     $built_structures);
$tpl->assign('votes_called',         $votes_called);

// Show the template
$tpl->display('map_details.tpl.php');
?>

<?php
/**
 * Project:     Unvstats
 * File:        game_details.php
 *
 * For license and version information, see /index.php
 */

require_once 'core/init.inc.php';

$game_details = $db->GetRow("SELECT game_id,
                                    game_timestamp,
                                    game_map_id,
                                    game_match_id,
                                    game_winner,
                                    game_length
                             FROM games
                             WHERE game_id = ?",
                             array($_GET['game_id']));

if( !isset($game_details['game_map_id']) ):
  die ("game id not found");
endif;

$map = $db->GetRow("SELECT map_name,
                           if (map_longname != '', map_longname, map_name) AS game_map_name
                    FROM maps
                    WHERE map_id = ? ",
                    array($game_details['game_map_id']));

$players = $db->GetAll("SELECT player_name,
                               player_id,
                               player_is_bot,
                               stats_kills,
                               stats_assists,
                               stats_teamkills,
                               stats_enemyassists,
                               stats_deaths,
                               stats_score,
                               IF(stats_time_alien = 0, NULL, SEC_TO_TIME( stats_time_alien )) AS time_alien,
                               IF(stats_time_human = 0, NULL, SEC_TO_TIME( stats_time_human )) AS time_human,
                               IF(stats_time_spec = 0, NULL, SEC_TO_TIME( stats_time_spec )) AS time_spec
                        FROM per_game_stats
                        INNER JOIN players ON stats_player_id = player_id
                        WHERE stats_game_id = ?
                          AND ".player_bot_filter_sql('players')."
                        GROUP BY player_id
                        ORDER BY stats_score DESC",
                        array($_GET['game_id']));

$gameplay_stats = null;
$gameplay_stats_path = gameplay_stats_file_path($game_details['game_match_id']);
if ($gameplay_stats_path !== null) {
  $gameplay_stats = parse_gameplay_stats_file($gameplay_stats_path);
}

// Assign variables to template
$tpl->assign('game_details', $game_details);
$tpl->assign('map', $map);
$tpl->assign('players', $players);
$tpl->assign('gameplay_stats', $gameplay_stats);

// Show the template
$tpl->display('game_details.tpl.php');
?>

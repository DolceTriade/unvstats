<?php
/**
 * Project:     Unvstats
 * File:        index.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @link http://tremstats.dasprids.de/
 * @author Ben 'DASPRiD' Scholzen <mail@dasprids.de>
 * @package Tremstats
 * @version 0.6.0
 */
require_once 'core/init.inc.php';
require_once 'core/tremreport.class.php';
// Get current data
$reporter      = new TremulousReporter(SERVER_ADDRESS);
$server_status = $reporter->getStatus();

// Sort by score (kills)
if (!empty($server_status['humans'])):
  foreach ($server_status['humans'] as $key => $row) {
    $hkills[$key] = $row['kills'];
  }
  array_multisort( $hkills, SORT_NUMERIC, SORT_DESC, $server_status['humans'] );
endif;
if (!empty($server_status['aliens'])):
  foreach ($server_status['aliens'] as $key => $row) {
    $akills[$key] = $row['kills'];
  }
  array_multisort( $akills, SORT_NUMERIC, SORT_DESC, $server_status['aliens'] );
endif;


if (!empty($server_status['server_vars']['mapname'])):
$running_map = $db->GetRow("SELECT map_id,
                                   if (map_longname != '', map_longname, map_name) AS map_text_name,
                                   mapstat_games,
                                   mapstat_alien_wins,
                                   mapstat_human_wins,
                                   mapstat_ties + mapstat_draws AS ties
                            FROM maps
                            LEFT JOIN map_stats ON map_id = mapstat_id
                            WHERE map_name = ?
                            LIMIT 0, 1",
                            array($server_status['server_vars']['mapname']));
endif;

// Get saved data
$games      = $db->GetRow("SELECT COUNT(*) AS count FROM games WHERE ".game_nonempty_filter_sql('games')." AND game_winner != 'undefined'");
$alien_wins = $db->GetRow("SELECT COUNT(*) AS count FROM games WHERE ".game_nonempty_filter_sql('games')." AND game_winner = 'aliens'");
$human_wins = $db->GetRow("SELECT COUNT(*) AS count FROM games WHERE ".game_nonempty_filter_sql('games')." AND game_winner = 'humans'");
$ties       = $db->GetRow("SELECT COUNT(*) AS count FROM games WHERE ".game_nonempty_filter_sql('games')." AND (game_winner = 'tie' OR game_winner = 'draw')");

$last_game = $db->GetRow("SELECT game_id FROM games ORDER BY game_id DESC LIMIT 0, 1");
$last_game_id = isset($last_game['game_id']) ? (int)$last_game['game_id'] : 0;
$game_cutoff = $last_game_id - TRESHOLD_MAX_GAMES_PAUSED;

$top_player = $db->GetRow("SELECT player_id,
                                  player_name,
                                  player_total_efficiency
                           FROM players
                           WHERE player_games_played >= ?
                                 AND player_last_game_id > ?
                                 AND player_is_bot = FALSE
                           ORDER BY player_total_efficiency DESC
                           LIMIT 0, 1",
                           array(TRESHOLD_MIN_GAMES_PLAYED, $game_cutoff));
$top_assistant = $db->GetRow("SELECT player_id,
                                     player_name,
                                     IF (player_games_played = 0, 0, player_assists / player_games_played) AS average_assists_for_team
                               FROM players
                               WHERE player_games_played >= ?
                                     AND player_last_game_id > ?
                                     AND player_is_bot = FALSE
                               ORDER BY average_assists_for_team DESC
                               LIMIT 0, 1",
                               array(TRESHOLD_MIN_GAMES_PLAYED, $game_cutoff));
$top_feeder = $db->GetRow("SELECT player_id,
                                  player_name,
                                  IF (player_games_played = 0, 0, player_deaths_enemy / player_games_played) AS average_deaths_by_enemy
                           FROM players
                           WHERE player_games_played >= ?
                                 AND player_last_game_id > ?
                                 AND player_is_bot = FALSE
                           ORDER BY average_deaths_by_enemy DESC
                           LIMIT 0, 1",
                           array(TRESHOLD_MIN_GAMES_PLAYED, $game_cutoff));
$top_teamkiller = $db->GetRow("SELECT player_id,
                                      player_name,
                                      IF (player_games_played = 0, 0, player_teamkills / player_games_played) AS average_kills_to_team
                               FROM players
                               WHERE player_games_played >= ?
                                     AND player_last_game_id > ?
                                     AND player_is_bot = FALSE
                               ORDER BY average_kills_to_team DESC
                               LIMIT 0, 1",
                               array(TRESHOLD_MIN_GAMES_PLAYED, $game_cutoff));
$top_enemyassistant = $db->GetRow("SELECT player_id,
                                          player_name,
                                          IF (player_games_played = 0, 0, player_enemyassists / player_games_played) AS average_assists_for_enemy
                                   FROM players
                                   WHERE player_games_played >= ?
                                         AND player_last_game_id > ?
                                         AND player_is_bot = FALSE
                                   ORDER BY average_assists_for_enemy DESC
                                   LIMIT 0, 1",
                               array(TRESHOLD_MIN_GAMES_PLAYED, $game_cutoff));
$most_active_player = $db->GetRow("SELECT player_id,
                                          player_name,
                                          player_game_time_factor
                                   FROM players
                                   WHERE player_games_played >= ?
                                         AND player_last_game_id > ?
                                         AND player_is_bot = FALSE
                                   ORDER BY player_game_time_factor DESC
                                   LIMIT 0, 1",
                                   array(TRESHOLD_MIN_GAMES_PLAYED, $game_cutoff));

$top_score = $db->GetRow("SELECT player_id,
                                 player_name,
                                 stats_score
                          FROM per_game_stats
                          INNER JOIN players ON player_id = stats_player_id
                          WHERE player_last_game_id > ?
                                AND stats_score > 0
                                AND player_is_bot = FALSE
                          ORDER BY stats_score DESC
                          LIMIT 0, 1",
                          array($game_cutoff));

$top_skill = $db->GetRow("SELECT player_id,
                                 player_name,
                                 skill,
                                 skill_sigma
                          FROM players p,
                            (SELECT skill_player_id AS skill_player_id,
                                    skill_mu - 3 * skill_sigma AS skill,
                                    skill_sigma
                             FROM skill) s
                          WHERE s.skill_player_id = p.player_id
                            AND player_last_game_id > ?
                            AND player_is_bot = FALSE
                          ORDER BY skill DESC
                          LIMIT 0, 1",
                          array($game_cutoff));

$most_played_map = $db->GetRow("SELECT map_id,
                                       if (map_longname != '', map_longname, map_name) AS map_text_name,
                                       mapstat_games,
                                       mapstat_alien_wins,
                                       mapstat_human_wins,
                                       mapstat_ties + mapstat_draws AS ties
                                FROM map_stats
                                INNER JOIN maps ON map_id = mapstat_id
                                ORDER BY mapstat_games DESC
                                LIMIT 0, 1");

$state = $db->GetRow("SELECT log_timestamp FROM state WHERE log_id = '0'");

$overview = array();
$overview['games']      = $games['count'];
$overview['alien_wins'] = $alien_wins['count'];
$overview['human_wins'] = $human_wins['count'];
$overview['ties']       = $ties['count'];

$overview['top_player']         = $top_player;
$overview['top_assistant']      = $top_assistant;
$overview['top_feeder']         = $top_feeder;
$overview['top_teamkiller']     = $top_teamkiller;
$overview['top_enemyassistant'] = $top_enemyassistant;
$overview['most_active_player'] = $most_active_player;
$overview['top_score']          = $top_score;
$overview['top_skill']          = $top_skill;

$overview['most_played_map'] = $most_played_map ?: null;

// Assign variables to template
if (!empty($server_status)) $tpl->assign('server_status', $server_status);
if (!empty($running_map)) $tpl->assign('running_map',   $running_map);

$tpl->assign('overview',      $overview);

$tpl->assign('state',         $state);

// Show the template
$tpl->display('overview.tpl.php');
?>

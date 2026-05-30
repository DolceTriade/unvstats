<?php
/**
 * Project:     Unvstats
 * File:        most_active_players.php
 *
 * For license and version information, see /index.php
 */

require_once 'core/init.inc.php';

$last_game = $db->GetRow("SELECT game_id FROM games ORDER BY game_id DESC LIMIT 0, 1");
$last_game_id = isset($last_game['game_id']) ? (int)$last_game['game_id'] : 0;
$game_cutoff = $last_game_id - TRESHOLD_MAX_GAMES_PAUSED;

$custom_orders = array (
  'rank'           => 'player_rank',
  'player'         => 'player_name_uncolored',
  'kills'          => 'player_kills',
  'deaths'         => 'player_deaths',
  'efficiency'     => 'player_total_efficiency',
  'games'          => 'player_games_played',
  'gametimefactor' => 'player_game_time_factor',
  'skill'          => 'skill',
  'skill_a'        => 'skill_a',
  'skill_h'        => 'skill_h'
);
$order = get_custom_sort($custom_orders, 'rank');

$max_games_row = $db->GetRow("SELECT MAX(player_games_played) AS games FROM players");
$max_games = isset($max_games_row['games']) ? (int)$max_games_row['games'] : 0;
$min_games = $max_games - TRESHOLD_MIN_GAMES_PLAYED;
if ($min_games > TRESHOLD_MIN_GAMES_PLAYED)
    $min_games = TRESHOLD_MIN_GAMES_PLAYED;


$db->Execute("SET @n := 0");
$db->Execute("CREATE TEMPORARY TABLE tmp (
               SELECT player_id,
                      @n := @n + 1 AS player_rank,
                      player_name,
                      player_name_uncolored,
                      player_kills,
                      player_deaths,
                      player_total_efficiency,
                      player_games_played,
                      player_game_time_factor,
                      t.skill_mu - 3 * t.skill_sigma AS skill,
                      t.skill_sigma AS skill_sigma,
                      t.skill_alien_mu - 3 * t.skill_alien_sigma AS skill_a,
                      t.skill_alien_sigma AS skill_a_sigma,
                      t.skill_human_mu - 3 * t.skill_human_sigma AS skill_h,
                      t.skill_human_sigma AS skill_h_sigma
               FROM players
                 LEFT OUTER JOIN skill_last t
                   ON t.skill_player_id = players.player_id
               WHERE player_games_played >= ?
                     AND player_last_game_id > ?
                     AND player_is_bot = FALSE
               ORDER BY player_game_time_factor DESC
             )", array($min_games, $game_cutoff));


$pagelister->SetQuery("SELECT player_id,
                              player_rank,
                              player_name,
                              player_kills,
                              player_deaths,
                              player_total_efficiency,
                              player_games_played,
                              player_game_time_factor,
                              skill, skill_sigma,
                              skill_a, skill_a_sigma,
                              skill_h, skill_h_sigma
                       FROM tmp
                       ORDER BY ".$order."");

$top = $db->GetAll($pagelister->GetQuery());

// Assign variables to template
$tpl->assign('top', $top);

// Show the template
$tpl->display('most_active_players.tpl.php');
?>

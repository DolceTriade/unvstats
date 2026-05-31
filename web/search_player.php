<?php
/**
 * Project:     Unvstats
 * File:        search_player.php
 *
 * For license and version information, see /index.php
 */

require_once 'core/init.inc.php';

if (isset($_GET['query'])) {
  $custom_orders = array (
    'player'     => 'player_name_uncolored',
    'kills'      => 'player_kills',
    'deaths'     => 'player_deaths',
    'efficiency' => 'player_total_efficiency'
  );
  $order = get_custom_sort($custom_orders, 'player');
  $namelike = $db->qstr('%'.$_GET['query'].'%');

  $pagelister->SetQuery("SELECT player_id,
                                player_name,
                                player_kills,
                                player_deaths,
                                player_total_efficiency
                         FROM players
                         WHERE player_name_uncolored LIKE ".$namelike."
                           AND player_is_bot = FALSE
                         ORDER BY ".$order);
  $players = $db->GetAll($pagelister->GetQuery());
  
  // Maybe an alias?
  $players_tjw =  $db->GetAll("SELECT player_id,
                                   nick_name AS player_name,
                                   player_name  AS player_tjw_name,
                                   player_kills,
                                   player_deaths,
                                   player_total_efficiency
                            FROM players JOIN nicks ON players.player_id = nicks.nick_player_id
                            WHERE  nicks.nick_name_uncolored LIKE ".$namelike." AND nicks.nick_name_uncolored != players.player_name_uncolored
                               AND player_is_bot = FALSE
                            ORDER BY ".$order);

  // Assign variables to template
  $tpl->assign('players', array_merge($players, $players_tjw));
}

// Show the template
$tpl->display('search_player.tpl.php');
?>

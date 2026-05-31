<?php
/**
 * Project:     Unvstats
 * File:        games.php
 *
 * For license and version information, see /index.php
 */

require_once 'core/init.inc.php';

$map_search="";
if (isset($_GET['map_id'])) {
  $map_id = $_GET['map_id'];
  if (!is_numeric($map_id))
    $map_id = '0';
  $map_search="WHERE game_map_id = '".$map_id."'";
  $tpl->assign('map_id', $map_id);
}
if (!session_include_bots()) {
  if ($map_search !== "") {
    $map_search .= " AND";
  } else {
    $map_search .= "WHERE";
  }
  $map_search .= " game_is_empty = FALSE";
}
if (isset($_GET['hideempty']) and $_GET['hideempty'] == "1") {
  if ($map_search !== "") {
    $map_search.=" AND";
  } else {
    $map_search.="WHERE";
  }
  $map_search .= " game_is_empty = FALSE";
  $tpl->assign('hideempty', $_GET['hideempty']);
}

if (isset($_GET['order'])) {
  switch ($_GET['order']) {
    case "kills":
      $order="game_total_kills";
      $order_name="kills";
      break;
    case "deaths":
      $order="game_total_deaths";
      $order_name="deaths";
      break;
    case "length":
      $order="game_length";
      $order_name="length";
      break;
    }
}

if (isset($order_name)) {
  $tpl->assign('order_name', $order_name);
}

if (isset($order)) {
  $pagelister->SetQuery("SELECT game_id,
                                game_timestamp,
                                game_winner,
                                game_length,
                                if (map_longname != '', map_longname, map_name) AS game_map_name,
                                ".$order." AS stats_count
                         FROM games
                         INNER JOIN maps ON game_map_id = map_id
                         ".$map_search."
                         ORDER BY stats_count DESC");
}
else {
  $pagelister->SetQuery("SELECT game_id,
                                game_timestamp,
                                game_winner,
                                game_length,
                                if (map_longname != '', map_longname, map_name) AS game_map_name
                         FROM games
                         INNER JOIN maps ON game_map_id = map_id
                         ".$map_search."
                         ORDER BY game_id DESC");
}

$games = $db->GetAll($pagelister->GetQuery());

// Assign variables to template
$tpl->assign('games', $games);

// Show the template
$tpl->display('games.tpl.php');
?>

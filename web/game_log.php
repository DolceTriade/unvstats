<?php
/**
 * Project:     Unvstats
 * File:        game_log.php
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

$says = $db->GetAll("SELECT player_name,
                            player_id,
                            player_is_bot,
                            say_gametime,
                            say_mode,
                            say_message
                        FROM says
                        INNER JOIN players ON say_player_id = player_id
                        WHERE say_game_id = ?
                     UNION
                     SELECT 'console',
                            0,
                            FALSE,
                            say_gametime,
                            say_mode,
                            say_message
                        FROM says
                        WHERE say_game_id = ? AND say_player_id = 0
                        ",
                        array($_GET['game_id'], $_GET['game_id']));

$kills = $db->GetAll("SELECT players.player_name AS victim_name,
                             players.player_id AS victim_id,
                             players.player_is_bot AS victim_is_bot,
                             source.player_name AS killer_name,
                             source.player_id AS killer_id,
                             source.player_is_bot AS killer_is_bot,
                             source2.player_name AS assistant_name,
                             source2.player_id AS assistant_id,
                             source2.player_is_bot AS assistant_is_bot,
                             kill_gametime,
                             kill_type,
                             kill_assist_type,
                             weapon_name,
                             weapon_icon
                        FROM kills
                        INNER JOIN players ON kill_target_player_id = player_id
                        LEFT JOIN players AS source ON kill_source_player_id = source.player_id
                        LEFT JOIN players AS source2 ON kill_assist_player_id = source2.player_id
                        INNER JOIN weapons ON weapon_id = kill_weapon_id
                        WHERE kill_game_id = ?",
                        array($_GET['game_id']));

$destructs = $db->GetAll("SELECT player_name,
                                player_id,
                                player_is_bot,
                                destruct_gametime,
                                building_name,
                                building_icon,
                                building_team,
                                weapon_name,
                                weapon_icon,
                                weapon_team
                        FROM destructions
                        INNER JOIN players ON destruct_player_id = player_id
                        INNER JOIN buildings ON building_id = destruct_building_id
                        INNER JOIN weapons ON weapon_id = destruct_weapon_id
                        WHERE destruct_game_id = ?",
                        array($_GET['game_id']));

$builds = $db->GetAll("SELECT player_name,
                              player_id,
                              player_is_bot,
                              build_gametime,
                              building_name,
                              building_icon
                        FROM builds
                        INNER JOIN players ON build_player_id = player_id
                        INNER JOIN buildings ON building_id = build_building_id
                        WHERE build_game_id = ?",
                        array($_GET['game_id']));

$decons = $db->GetAll("SELECT player_name,
                              player_id,
                              player_is_bot,
                              decon_gametime,
                              building_name,
                              building_icon
                        FROM decons
                        INNER JOIN players ON decon_player_id = player_id
                        INNER JOIN buildings ON building_id = decon_building_id
                        WHERE decon_game_id = ?",
                        array($_GET['game_id']));

$votes = $db->GetAll("SELECT players.player_name AS caller_name,
                             players.player_id AS caller_id,
                             players.player_is_bot AS caller_is_bot,
                             victim.player_name AS victim_name,
                             victim.player_id AS victim_id,
                             victim.player_is_bot AS victim_is_bot,
                             vote_gametime,
                             vote_mode,
                             vote_type,
                             vote_arg,
                             vote_endtime,
                             vote_pass,
                             vote_yes,
                             vote_no
                        FROM votes
                        INNER JOIN players ON vote_player_id = player_id
                        LEFT JOIN players AS victim ON vote_victim_id = victim.player_id
                        WHERE vote_game_id = ?",
                        array($_GET['game_id']));

if (!session_include_bots()) {
  $says = array_values(array_filter($says, function ($say) {
    return empty($say['player_is_bot']);
  }));
  $kills = array_values(array_filter($kills, function ($kill) {
    return empty($kill['victim_is_bot']) &&
           empty($kill['killer_is_bot']) &&
           empty($kill['assistant_is_bot']);
  }));
  $destructs = array_values(array_filter($destructs, function ($destruct) {
    return empty($destruct['player_is_bot']);
  }));
  $builds = array_values(array_filter($builds, function ($build) {
    return empty($build['player_is_bot']);
  }));
  $decons = array_values(array_filter($decons, function ($decon) {
    return empty($decon['player_is_bot']);
  }));
  $votes = array_values(array_filter($votes, function ($vote) {
    return empty($vote['caller_is_bot']) && empty($vote['victim_is_bot']);
  }));
}


$N=1;
foreach ($kills as $kill):
  $logs[$kill['kill_gametime'].'.'.$N++]=$kill;
endforeach;

foreach ($builds as $build):
  $logs[$build['build_gametime'].'.'.$N++]=$build;
endforeach;

foreach ($decons as $decon):
  $logs[$decon['decon_gametime'].'.'.$N++]=$decon;
  $N++;
endforeach;

foreach ($destructs as $destruct):
  $logs[$destruct['destruct_gametime'].'.'.$N++]=$destruct;
endforeach;

if( constant('PRIVACY_CHAT') != '1' ):
  foreach ($says as $say):
    $logs[$say['say_gametime'].'.'.$N++]=$say;
  endforeach;
endif;

foreach ($votes as $vote):
  $logs[$vote['vote_gametime'].'.'.$N++]=$vote;
  if( !empty( $vote['vote_endtime'] ) ):
    $logs[$vote['vote_endtime'].'.'.$N++]=
      array( 'endvote_gametime' => $vote['vote_endtime'],
             'endvote_mode' => $vote['vote_mode'],
             'endvote_pass' => $vote['vote_pass'],
             'endvote_yes' => $vote['vote_yes'],
             'endvote_no' => $vote['vote_no'] );
  endif;
endforeach;

$logs[$game_details['game_length'].'.'.$N++]=$game_details;

function add_misc($gametime, $action, $text, $salt) {
  global $logs;
  if (!empty($gametime)):
    $key = $gametime.'.'.$salt;
    $data['misc_gametime'] = $gametime;
    $data['misc_action'] = $action;
    $data['misc_text'] = $text;
    $logs[$key] = $data;
  endif;
}

// Assign variables to template
$tpl->assign('game_details', $game_details);
$tpl->assign('map', $map);
$tpl->assign('says', $says);
$tpl->assign('kills', $kills);

if (isset($logs) ):
  ksort ($logs, SORT_STRING);
  $tpl->assign('logs', $logs);
endif;

// Show the template
$tpl->display('game_log.tpl.php');
?>

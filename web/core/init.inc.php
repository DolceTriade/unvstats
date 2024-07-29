<?php
/**
 * Project:     Unvstats
 * File:        init.inc.php
 *
 * For license and version information, see /index.php
 */
$calculation_start = microtime(true);

if (function_exists('locale_set_default'))
  locale_set_default('POSIX');

define('VERSION', '0.3.0');

define('CLIENT_IS_BOT', preg_match( '/apache|bot|catalog|cr[ao]wler|digg|https?:|facebook|feed|monitor|spider|syndication|yahoo/i', $_SERVER['HTTP_USER_AGENT'] ) ? true : false);
define('CLIENT_IS_TEXT', preg_match( '/^(?:lynx|links|elinks|w3m)\b/i', $_SERVER['HTTP_USER_AGENT'] ) ? true : false);

require_once dirname(__FILE__).'/config.inc.php';
require_once dirname(__FILE__).'/lib.inc.php';
require_once dirname(__FILE__).'/graphs.inc.php';
require_once dirname(__FILE__).'/tiny_templating.class.php';
require_once dirname(__FILE__).'/adodb/adodb-exceptions.inc.php';
require_once dirname(__FILE__).'/adodb/adodb.inc.php';
require_once dirname(__FILE__).'/pagelister/PageLister.class.php';
// Connect to MySQL
try {
  $db = NewADOConnection('mysql');
  $db->Connect(MYSQL_HOSTNAME, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DATABASE);
  $db->Execute('SET time_zone = `+00:00`');
} catch (exception $e) {
  echo($e);
  die;
}

// Set the page lister
$pagelister = new PageLister();
$pagelister->SetEntriesPerPage(TREMSTATS_EPP);

$counthandler = new PageLister_CountHandler();
$counthandler->SetHandler('AdoDB_Count_Handler');
$counthandler->SetArgs(array($db));

$pagelister->SetCountHandler($counthandler);

// Initiate the template engine
$tpl = new tiny_templating(TREMSTATS_TEMPLATE, TREMSTATS_SKIN);
$tpl->assign('calculation_start', $calculation_start);
$tpl->assign('pagelister',        $pagelister);

$bots = $db->GetAll("SELECT `players`.`player_id` AS `player_id` FROM `players` WHERE `players`.`player_is_bot` = TRUE");
foreach ($bots as $k => $v)
  $bots[$k] = $v[0];
?>

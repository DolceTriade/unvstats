<?php
/**
 * Project:     Unvstats
 * File:        lib.inc.php
 *
 * For license and version information, see /index.php
 */

function AdoDB_Count_Handler ($query, $args) {
  $rs = $args[0]->Execute($query);

  $entriesTotal = $rs->RecordCount();
  $rs->Close();

  return $entriesTotal;
}

enum ColorType {
  case SINGLE;
  case HEX_HASH;
  case HEX_X_SHORT;
  case HEX_X_LONG;
}

function color_type_str($typ) {
  switch ($typ) {
    case ColorType::SINGLE:
      return 'SINGLE';
    case ColorType::HEX_HASH:
      return 'HEX_HASH';
    case ColorType::HEX_X_SHORT:
      return 'HEX_X_SHORT';
    case ColorType::HEX_X_LONG:
      return 'HEX_X_LONG';
  }
  return 'UNKNOWN';
}

function is_hex_string($str) {
  return preg_match('/^[A-Fa-f0-9]+$/', $str) === 1;
}

function get_color_type($str, $offs) {
  $len = strlen($str);
  if ($str[$offs] !== '^') return false;
  if ($len > $offs + 7 &&
      $str[$offs + 1] === '#' &&
      is_hex_string(substr($str, $offs + 2, 6))) {
    return ColorType::HEX_HASH;
  }
  if ($len > $offs + 4 &&
      ($str[$offs + 1] === 'x' || $str[$offs + 1] === 'X') &&
      is_hex_string(substr($str, $offs + 2, 3))) {
    if ($len > $offs + 7 && is_hex_string(substr($str, $offs + 2, 6))) {
      return ColorType::HEX_X_LONG;
    }
    return ColorType::HEX_X_SHORT;
  }
  if ($len > $offs + 1 && ord($str[$offs + 1]) >= ord('0') && ord(strtoupper($str[$offs + 1])) <= ord('O')) {
    return ColorType::SINGLE;
  }
  return false;
}

function strip_color_codes($string) {
  $len = strlen($string);
  $result = '';

  for ($i = 0; $i < $len; $i++) {
    if ($string[$i] !== '^') {
      $result .= $string[$i];
      continue;
    }

    if ($i + 1 < $len && $string[$i + 1] === '^') {
      $result .= '^';
      $i++;
      continue;
    }

    $color_type = get_color_type($string, $i);
    if ($color_type === false) {
      $result .= '^';
      continue;
    }

    if ($color_type === ColorType::SINGLE) {
      $i += 1;
    } else if ($color_type === ColorType::HEX_HASH || $color_type === ColorType::HEX_X_LONG) {
      $i += 7;
    } else if ($color_type === ColorType::HEX_X_SHORT) {
      $i += 4;
    }
  }

  return $result;
}

function get_color_style($string, $pos, $color_type) {
  if ($color_type === ColorType::SINGLE) {
    return array(
      'style' => '<span class="quakecolor_'.(31 - abs(ord(strtoupper($string[$pos + 1])) - ord('O'))).'">',
      'length' => 2,
    );
  }

  if ($color_type === ColorType::HEX_HASH) {
    return array(
      'style' => '<span style="color: '.substr($string, $pos + 1, 7).';">',
      'length' => 8,
    );
  }

  if ($color_type === ColorType::HEX_X_LONG) {
    return array(
      'style' => '<span style="color: #'.substr($string, $pos + 2, 6).';">',
      'length' => 8,
    );
  }

  $hex = substr($string, $pos + 2, 3);
  return array(
    'style' => '<span style="color: #'.$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2].';">',
    'length' => 5,
  );
}

function replace_color_codes ($string) {
  // escape html reserved chars
  $string = htmlspecialchars($string, ENT_QUOTES);
  $pos = $oldpos = 0;
  $result = '';
  $color_open = false;
  while (true) {
    $pos = strpos($string, "^", $oldpos);
    if ($pos === false) {
      if ($oldpos === 0) {
        return $string;
      }
      // Close previous tag and return the rest of the string.
      $result .= substr($string, $oldpos);
      if ($color_open) $result .= '</span>';
      return $result;
    }

    $color_type = get_color_type($string, $pos);
    if ($color_type === false) {
      $result .= substr($string, $oldpos, $pos - $oldpos + 1);
      $oldpos = $pos + 1;
      continue;
    }

    $result .= substr($string, $oldpos, $pos - $oldpos);

    if ($color_open) {
      $result .= '</span>';
      $color_open = false;
    }

    $color_data = get_color_style($string, $pos, $color_type);
    $color_open = true;
    $result .= $color_data['style'];
    $oldpos = $pos + $color_data['length'];
  }
}

function custom_sort ($sort_title, $sort_name) {
  $current_file = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $custom_sort  = (isset($_GET['custom_sort']) ? $_GET['custom_sort']: null);
  $custom_dir   = (isset($_GET['custom_dir']) ? $_GET['custom_dir']: 'desc');

/*
  if ($return) {
    return array(
      'custom_sort' => $custom_sort,
      'custom_dir'  => $custom_dir
    );
  }
*/

  if ($custom_sort == $sort_name) {
    $new_dir = ($custom_dir == 'desc' ? 'asc': 'desc');

    $arrow = ($custom_dir == 'desc' ? '↑': '↓');
  } else {
    $arrow = '';
    $new_dir = 'desc';
  }

  $additional_string = '';
  if (is_array($_GET)) {
    foreach ($_GET AS $key => $value) {
      if ($key == 'custom_sort' || $key == 'custom_dir') continue;

      $additional_string .= '&amp;'.htmlspecialchars($key).'='.htmlspecialchars(($value));
    }
  }

  return '<a href="'.$current_file.'?custom_sort='.urlencode($sort_name).'&amp;custom_dir='.$new_dir.$additional_string.'">'.$arrow.' '.$sort_title.'</a>';
}

function get_custom_sort ($custom_orders, $default_order) {
  $custom_sort  = (isset($_GET['custom_sort']) ? $_GET['custom_sort']: null);
  $custom_dir   = (isset($_GET['custom_dir']) ? $_GET['custom_dir']: 'asc');

  if (!in_array($custom_dir, array('asc', 'desc'))) $custom_dir = 'asc';

  if (is_null($custom_sort)) {
    $_GET['custom_sort'] = $default_order;
    $_GET['custom_dir']  = 'asc';

    return $custom_orders[$default_order].' ASC';
  } else {
    if (!array_key_exists($custom_sort, $custom_orders)) {
      $_GET['custom_sort'] = $default_order;
      $_GET['custom_dir']  = 'asc';

      return $custom_orders[$default_order].' ASC';
    } else {
      return $custom_orders[$custom_sort].' '.strtoupper($custom_dir);
    }
  }
}

function session_include_bots() {
  return !empty($_SESSION['include_bots']);
}

function set_include_bots_from_request() {
  if (!isset($_GET['include_bots'])) {
    if (!isset($_SESSION['include_bots'])) {
      $_SESSION['include_bots'] = false;
    }
    return;
  }

  $_SESSION['include_bots'] = $_GET['include_bots'] === '1';
}

function player_bot_filter_sql($alias = 'players') {
  if (session_include_bots()) {
    return '1 = 1';
  }

  return sprintf('%s.`player_is_bot` = FALSE', $alias);
}

function game_nonempty_filter_sql($alias = 'games') {
  return sprintf('%s.`game_is_empty` = FALSE', $alias);
}

function current_url_with_params($params = array()) {
  $query = $_GET;
  foreach ($params as $key => $value) {
    if ($value === null) {
      unset($query[$key]);
    } else {
      $query[$key] = $value;
    }
  }

  $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $queryString = http_build_query($query);
  if ($queryString === '') {
    return $path;
  }

  return $path . '?' . $queryString;
}

function include_bots_toggle_url() {
  return current_url_with_params(array(
    'include_bots' => session_include_bots() ? '0' : '1',
  ));
}

function gameplay_stats_file_path($match_id) {
  if (!defined('GAMEPLAY_STATS_DIR') || GAMEPLAY_STATS_DIR === '') {
    return null;
  }

  if ($match_id === null || $match_id === '') {
    return null;
  }

  $path = rtrim(GAMEPLAY_STATS_DIR, '/').'/'.$match_id.'.log';
  if (!is_file($path)) {
    return null;
  }

  return $path;
}

function parse_gameplay_stats_file($path) {
  $samples = array();
  $raw_events = array();
  $events = array();
  $match_id = '';

  $handle = @fopen($path, 'r');
  if ($handle === false) {
    return null;
  }

  while (($raw = fgets($handle)) !== false) {
    $line = trim($raw);
    if ($line === '') {
      continue;
    }

    if (strpos($line, '# MatchId:') === 0) {
      $match_id = trim(substr($line, strlen('# MatchId:')));
      continue;
    }

    if ($line[0] === '#') {
      continue;
    }

    if (preg_match('/^EVT\s+([0-9]+)\s+(\S+)\s+([0-9]+)\s+(\S+)\s+(.+?)\s+([0-9]+)$/', $line, $matches)) {
      $team = $matches[2];
      if ($team === 'alien') {
        $team = 'aliens';
      } elseif ($team === 'human') {
        $team = 'humans';
      }

      $raw_events[] = array(
        't' => (int)$matches[1],
        'team' => $team,
        'client' => (int)$matches[3],
        'kind' => $matches[4],
        'item' => trim($matches[5]),
        'cost' => (int)$matches[6],
      );
      continue;
    }

    if (!preg_match('/^([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+(-?[0-9]+)\s+(-?[0-9]+)\s+(-?[0-9]+)\s+(-?[0-9]+)$/', $line, $matches)) {
      continue;
    }

    $samples[] = array(
      't' => (int)$matches[1],
      'aliens' => (int)$matches[2],
      'humans' => (int)$matches[3],
      'alien_net' => (int)$matches[4],
      'human_net' => (int)$matches[5],
      'alien_spent' => (int)$matches[6],
      'human_spent' => (int)$matches[7],
    );
  }

  fclose($handle);

  $cumulative = array(
    'aliens' => 0,
    'humans' => 0,
  );
  foreach ($raw_events as $event) {
    if (array_key_exists($event['team'], $cumulative)) {
      $cumulative[$event['team']] += $event['cost'];
      $event['cumulative_spent'] = $cumulative[$event['team']];
    } else {
      $event['cumulative_spent'] = $event['cost'];
    }
    $events[] = $event;
  }

  return array(
    'match_id' => $match_id,
    'samples' => $samples,
    'events' => $events,
  );
}
?>

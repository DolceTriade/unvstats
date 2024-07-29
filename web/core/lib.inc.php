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
  case HEX;
}

function color_type_str($typ) {
  switch ($typ) {
    case ColorType::SINGLE:
      return 'SINGLE';
    case ColorType::HEX:
      return 'HEX';
  }
  return 'UNKNOWN';
}

function get_color_type($str, $offs) {
  $len = strlen($str);
  if ($str[$offs] !== '^') return false;
  if ($len > $offs + 7 &&
      $str[$offs + 1] === '#' &&
      ctype_xdigit(substr($str, $offs + 2, 6))) {
    return ColorType::HEX;
  }
  if ($len > $offs + 1 && ord($str[$offs + 1]) >= ord('0') && ord(strtoupper($str[$offs + 1])) <= ord('O')) {
    return ColorType::SINGLE;
  }
  error_log('s='.$str . ' ' . substr($str, $offs + 1, 6));
  return false;
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
    error_log($string . ' ' . color_type_str($color_type));
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

    if ($color_type === ColorType::SINGLE) {
      $color_open = true;
      $result .= '<span class="quakecolor_'.(31 - abs(ord(strtoupper($string[$pos + 1])) - ord('O'))).'">';
      $oldpos = $pos + 2;
    } else if ($color_type === ColorType::HEX) {
      $result .= '<span style="color: '.substr($string, $pos+1, 7).';">';
      $oldpos = $pos + 8;
    }
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
?>

<?php
/**
 * Project:     Unvstats
 * File:        player_sig.php
 *
 * For license and version information, see /index.php
 */

require_once 'core/init.inc.php';

if (!isset($_GET['player_id'])) {
  die('No player id given');
}

// basic info
$servername = SERVER_NAME;

switch( $_GET['style'] ) {
  case 1:
  case 2:
    $w = 500;
    $h = 40;
    break;
  default:
    $w = 400;
    $h = 46;
    break;
}

// Get the data
$stats = $db->GetRow("SELECT player_id,
                             player_name,
                             player_kills,
                             player_deaths
                      FROM players
                      WHERE player_id = ?",
                      array($_GET['player_id']));

if( !isset($stats['player_id']) ):
  die ("player id not found");
endif;

if( constant('PRIVACY_QUOTE') != '1' ):
  $random_quote = $db->GetRow("SELECT say_message
                               FROM says
                               WHERE say_player_id = ?
                               ORDER BY RAND()
                               LIMIT 0, 1",
                               array($_GET['player_id']));
endif;

$rgb = array(
  array(  51,  51,  51 ),
  array( 255,   0,   0 ),
  array(   0, 255,   0 ),
  array( 255, 255,   0 ),
  array(   0,   0, 255 ),
  array(   0, 255, 255 ),
  array( 255,   0, 255 ),
  array( 255, 255, 255 ),
  array( 255, 128,   0 ),
  array( 128, 128, 128 ),
  array( 191, 191, 191 ),
  array( 191, 191, 191 ),
  array(   0, 128,   0 ),
  array( 128, 128,   0 ),
  array(   0,   0, 128 ),
  array( 128,   0,   0 ),
  array( 128,  64,   0 ),
  array( 255, 153,  26 ),
  array(   0, 128, 128 ),
  array( 128,   0, 128 ),
  array(   0, 128, 255 ),
  array( 128,   0, 255 ),
  array(  51, 153, 204 ),
  array( 204, 255, 204 ),
  array(   0, 102,  51 ),
  array( 255,   0,  51 ),
  array( 179,  26,  26 ),
  array( 153,  51,   0 ),
  array( 204, 153,  51 ),
  array( 153, 153,  51 ),
  array( 255, 255, 191 ),
  array( 255, 255, 128 )
);


// safe to start image at this point
header("Content-type: image/png");


$im = imagecreatetruecolor($w, $h);

for($i = 0; $i < 32; $i++)
{
  $colors[$i]['alloc'] = imagecolorallocate($im, $rgb[$i][0], $rgb[$i][1], $rgb[$i][2]);
  $colors[$i]['alpha'] = imagecolorallocatealpha($im, $rgb[$i][0], $rgb[$i][1], $rgb[$i][2], 64);
}

function char_width( $size )
{
  switch( $size ) {
    case 1: return 5;
    case 2: return 6;
    case 3: return 7;
    case 4: return 8;
    default:
        break;
    }

  return 9;
}

function sig_color_code_length($color_type)
{
  if ($color_type === ColorType::SINGLE) {
    return 2;
  }

  if ($color_type === ColorType::HEX_X_SHORT) {
    return 5;
  }

  return 8;
}

function sig_color_value($string, $pos, $color_type, $im, $colors, $alpha)
{
  if ($color_type === ColorType::SINGLE) {
    $c = $string[$pos + 1];
    if ($c === '*') {
      $c = '7';
    }
    $c = (ord($c) - 48) & 31;
    return $alpha ? $colors[$c]['alpha'] : $colors[$c]['alloc'];
  }

  if ($color_type === ColorType::HEX_HASH) {
    $hex = substr($string, $pos + 2, 6);
  } else if ($color_type === ColorType::HEX_X_LONG) {
    $hex = substr($string, $pos + 2, 6);
  } else {
    $hex = substr($string, $pos + 2, 3);
    $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
  }

  $rgb = hexdec($hex);
  $r = ($rgb >> 16) & 0xFF;
  $g = ($rgb >> 8) & 0xFF;
  $b = $rgb & 0xFF;

  if ($alpha) {
    return imagecolorallocatealpha($im, $r, $g, $b, 40);
  }

  return imagecolorallocate($im, $r, $g, $b);
}

function sig_escaped_color_sequence($string, $pos)
{
  $len = strlen($string);
  if ($pos + 1 >= $len || $string[$pos] !== '^' || $string[$pos + 1] !== '^') {
    return false;
  }

  $run_end = $pos + 2;
  while ($run_end < $len && $string[$run_end] === '^') {
    $run_end++;
  }

  $color_pos = $run_end - 1;
  $color_type = get_color_type($string, $color_pos);
  if ($color_type === false) {
    return false;
  }

  return array(
    'color_pos' => $color_pos,
    'color_type' => $color_type,
  );
}

function sig_rounded_rect($im, $x1, $y1, $x2, $y2, $radius, $color)
{
  imagefilledrectangle($im, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
  imagefilledrectangle($im, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
  imagefilledellipse($im, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
  imagefilledellipse($im, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
  imagefilledellipse($im, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
  imagefilledellipse($im, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
}

function sig_stat_chip($im, $x, $y, $label, $value, $bg, $fg)
{
  $width = 84;
  $height = 18;
  sig_rounded_rect($im, $x, $y, $x + $width, $y + $height, 4, $bg);
  imagestring($im, 1, $x + 6, $y + 3, strtoupper($label), $fg);
  $value_x = $x + $width - 8 - (strlen((string)$value) * char_width(2));
  imagestring($im, 2, $value_x, $y + 2, $value, $fg);
}

function sig_fit_plain_text($string, $size, $max_width)
{
  $plain = strip_color_codes($string);
  if (string_width($size, $plain) <= $max_width) {
    return $plain;
  }

  $ellipsis = '...';
  $ellipsis_width = string_width($size, $ellipsis);
  $result = '';

  for ($i = 0, $l = strlen($plain); $i < $l; $i++) {
    $next = $result . $plain[$i];
    if (string_width($size, $next) + $ellipsis_width > $max_width) {
      break;
    }
    $result = $next;
  }

  if ($result === '') {
    return '';
  }

  return $result . $ellipsis;
}

function string_width( $size, $string ) {
  $w = 0;

  for ($i = 0, $l = strlen($string); $i < $l; $i++) {
    $c = $string[$i];

    if ($c === '^') {
      $escaped_color = sig_escaped_color_sequence($string, $i);
      if ($escaped_color !== false) {
        $w += char_width($size);
        $i = $escaped_color['color_pos'] + sig_color_code_length($escaped_color['color_type']) - 1;
        continue;
      }

      if ($i + 1 < $l && $string[$i + 1] === '^') {
        $w += char_width($size);
        $i++;
        continue;
      }

      $color_type = get_color_type($string, $i);
      if ($color_type !== false) {
        $i += sig_color_code_length($color_type) - 1;
        continue;
      }
    }
    $w += char_width( $size );
  }
  return $w;
}

function color_print( $x, $y, $string, $im, $colors, $size, $alpha ) {
  if($alpha == 1) {
    $color = $colors[7]['alpha'];
  } else {
    $color = $colors[7]['alloc'];
  }

  for ($i = 0, $l = strlen($string); $i < $l; $i++) {
    $c = $string[$i];

    if ($c === '^') {
      $escaped_color = sig_escaped_color_sequence($string, $i);
      if ($escaped_color !== false) {
        imagechar($im, $size, $x, $y, '^', $color);
        $x += char_width($size);
        $color = sig_color_value($string, $escaped_color['color_pos'], $escaped_color['color_type'], $im, $colors, $alpha);
        $i = $escaped_color['color_pos'] + sig_color_code_length($escaped_color['color_type']) - 1;
        continue;
      }

      if ($i + 1 < $l && $string[$i + 1] === '^') {
        $c = '^';
        $i++;
      } else {
        $color_type = get_color_type($string, $i);
        if ($color_type !== false) {
          $color = sig_color_value($string, $i, $color_type, $im, $colors, $alpha);
          $i += sig_color_code_length($color_type) - 1;
          continue;
        }
      }
    }
    imagechar( $im, $size, $x, $y, $c, $color );
    $x += char_width( $size );
  }
}

function draw_style_zero($im, $w, $h, $stats, $servername, $random_quote, $colors)
{
  $bg = imagecolorallocate($im, 0x11, 0x16, 0x1d);
  $panel = imagecolorallocate($im, 0x1a, 0x23, 0x2d);
  $cyan = imagecolorallocate($im, 0x69, 0xdf, 0xfa);
  $red = imagecolorallocate($im, 0xbf, 0x40, 0x40);
  $ink = imagecolorallocate($im, 0xec, 0xf5, 0xff);
  $muted = imagecolorallocate($im, 0xa7, 0xb7, 0xc8);
  $chip = imagecolorallocate($im, 0x2a, 0x34, 0x40);

  imagefilledrectangle($im, 0, 0, $w - 1, $h - 1, $bg);
  sig_rounded_rect($im, 4, 4, $w - 5, $h - 5, 7, $panel);
  imagefilledrectangle($im, 4, 4, 11, $h - 5, $cyan);
  imagefilledrectangle($im, 11, 4, 14, $h - 5, $red);

  color_print(20, 8, $stats['player_name'], $im, $colors, 5, 0);
  sig_stat_chip($im, 216, 6, 'Kills', $stats['player_kills'], $chip, $ink);
  sig_stat_chip($im, 304, 6, 'Deaths', $stats['player_deaths'], $chip, $ink);
  $server = sig_fit_plain_text($servername, 1, 172);
  imagestring($im, 1, 216, 30, $server, $muted);

  if (!empty($random_quote['say_message'])) {
    $quote = strip_color_codes($random_quote['say_message']);
    $quote = substr($quote, 0, 34);
    imagestring($im, 1, 20, 30, $quote, $muted);
  }
}

function draw_style_one($im, $w, $h, $stats, $servername, $colors)
{
  $bg = imagecolorallocate($im, 0x08, 0x10, 0x18);
  $top = imagecolorallocate($im, 0x0f, 0x1c, 0x2a);
  $panel = imagecolorallocate($im, 0x13, 0x23, 0x33);
  $cyan = imagecolorallocate($im, 0x69, 0xdf, 0xfa);
  $red = imagecolorallocate($im, 0xbf, 0x40, 0x40);
  $ink = imagecolorallocate($im, 0xf2, 0xf8, 0xff);
  $muted = imagecolorallocate($im, 0x8f, 0xa6, 0xbb);
  $line = imagecolorallocate($im, 0x2a, 0x3d, 0x52);

  imagefilledrectangle($im, 0, 0, $w - 1, $h - 1, $bg);
  imagefilledrectangle($im, 0, 0, $w - 1, 11, $top);
  sig_rounded_rect($im, 8, 8, $w - 9, $h - 8, 6, $panel);
  imagefilledrectangle($im, 18, 14, 21, $h - 14, $cyan);
  imagefilledrectangle($im, 24, 14, 27, $h - 14, $red);
  imageline($im, 180, 12, 180, $h - 12, $line);
  imageline($im, 334, 12, 334, $h - 12, $line);

  color_print(34, 13, $stats['player_name'], $im, $colors, 5, 0);
  imagestring($im, 1, 188, 13, 'SERVER', $muted);
  $server = sig_fit_plain_text($servername, 1, 140);
  imagestring($im, 1, 188, 24, $server, $ink);
  imagestring($im, 1, 350, 13, 'KILLS', $muted);
  imagestring($im, 2, 350, 23, (string)$stats['player_kills'], $ink);
  imagestring($im, 1, 421, 13, 'DEATHS', $muted);
  imagestring($im, 2, 421, 23, (string)$stats['player_deaths'], $ink);
}

function draw_style_two($im, $w, $h, $stats, $servername, $random_quote, $colors)
{
  $bg = imagecolorallocate($im, 0x0d, 0x0f, 0x14);
  $card = imagecolorallocate($im, 0x17, 0x1b, 0x24);
  $band = imagecolorallocate($im, 0x22, 0x29, 0x35);
  $cyan = imagecolorallocate($im, 0x69, 0xdf, 0xfa);
  $red = imagecolorallocate($im, 0xbf, 0x40, 0x40);
  $ink = imagecolorallocate($im, 0xf7, 0xfb, 0xff);
  $muted = imagecolorallocate($im, 0xa4, 0xb1, 0xc1);
  $chip = imagecolorallocate($im, 0x2a, 0x31, 0x3d);

  imagefilledrectangle($im, 0, 0, $w - 1, $h - 1, $bg);
  sig_rounded_rect($im, 6, 6, $w - 7, $h - 7, 8, $card);
  sig_rounded_rect($im, 12, 12, 160, $h - 12, 5, $band);
  imagefilledrectangle($im, 16, 12, 19, $h - 12, $cyan);
  imagefilledrectangle($im, 19, 12, 22, $h - 12, $red);

  color_print(30, 15, $stats['player_name'], $im, $colors, 2, 0);
  sig_stat_chip($im, 158, 11, 'Kills', $stats['player_kills'], $chip, $ink);
  sig_stat_chip($im, 248, 11, 'Deaths', $stats['player_deaths'], $chip, $ink);
  $server = sig_fit_plain_text($servername, 1, 146);
  imagestring($im, 1, 340, 8, $server, $ink);

  if (!empty($random_quote['say_message'])) {
    $quote = strip_color_codes($random_quote['say_message']);
    $quote = substr($quote, 0, 26);
    imagestring($im, 1, 340, 20, $quote, $muted);
  }
}

switch( $_GET['style'] ) {
 case 1:
  draw_style_one($im, $w, $h, $stats, $servername, $colors);
  break;
 case 2:
  draw_style_two($im, $w, $h, $stats, $servername, $random_quote, $colors);
  break;
default:
  draw_style_zero($im, $w, $h, $stats, $servername, $random_quote, $colors);
  break;
}


imagepng($im);
imagedestroy($im);

?>

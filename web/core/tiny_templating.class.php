<?php
/**
 * Project:     Unvstats
 * File:        tiny_templating.inc.php
 *
 * For license and version information, see /index.php
 */
#[AllowDynamicProperties]
class tiny_templating {
  /**
   * Template to use
   *
   * @var string
   */
  private $template;

  /**
   * Skin to use
   *
   * @var string
   */
  private $skin;

  /**
   * Assigned variables
   *
   * @var array
   */
  private $vars = array();

  /**
   * Constructor function
   *
   * @param string $template
   * @param string $skin
   */
  function __construct($template, $skin) {
    $this->template = $template;

    $this->skin = $skin;
  }

  /**
   * Assign variables to the template
   *
   * @param string $var
   * @param mixed $value
   */
  public function assign ($var, $value) {
    $this->{$var} = $value;
  }

  /**
   * Display a template
   *
   * @param string $template
   */
  public function display ($template) {
    include 'templates/'.$this->template.'/'.$template;
  }

  /**
   * Generate the path to the CSS file
   *
   * @return string
   */
  private function css_file () {
    return htmlspecialchars('skins/'.$this->template.'/'.$this->skin.'/skin.css');
  }

  /**
   * Generate the path to the images
   *
   * @return string
   */
  private function image ($image) {
    return htmlspecialchars('skins/'.$this->template.'/'.$this->skin.'/images/'.$image);
  }
}
?>

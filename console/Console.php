<?php

namespace neptune\console;

/**
 * Console
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Console {

  protected static $instance;
  protected $readline;
  protected $fg_colour;
  protected $bg_colour;
  protected $error_fg_colour;
  protected $error_bg_colour;

  protected function __construct() {
    $this->readline = extension_loaded('readline');
  }

  public static function getInstance() {
    if(!self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function write($string, $new_line = true) {
    if ($new_line) {
      echo $string . PHP_EOL;
    } else {
      echo $string;
    }
  }

  public function error($string) {
    echo $string;
  }

  public function read($prompt = null) {
    if ($this->readline) {
      return readline($prompt);
    }
    $this->write($prefix, false);
    return fgets(STDIN);
  }

}
?>

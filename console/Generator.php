<?php

namespace neptune\console;

use neptune\console\Console;

/**
 * Generator
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Generator {

  protected static $instance;
  protected $dirs = array(
    'application/controller',
    'application/model',
    'application/view',
    'lib',
    'public',
    'scripts'
  );
  protected $writable_dirs = array(
    'storage/logs',
    'config'
  );

  protected function __construct() {
    $this->console = console::getInstance();
  }

  public static function getInstance() {
    if(!self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function populateAppDirectory($root) {
    if(!is_dir($root)) {
      echo $root . ' is not a directory.';
      return false;
    }
    if(substr($root, -1) != '/') {
      $root .= '/';
    }
    foreach($this->dirs as $dir) {
      if(!file_exists($root . $dir)) {
        mkdir($root . $dir, 0755, true);
        $this->console->write('Creating directory ' . $root.$dir);
      }
    }
    //todo::move the chmod stuff to an install script that can be run when put on a server
    foreach($this->writable_dirs as $dir) {
      if(!file_exists($root . $dir)) {
        mkdir($root . $dir, 0777, true);
        chmod($root . $dir, 0777);
        $this->console->write('Creating directory ' . $root.$dir);
        $this->console->write('Setting permissions to 777: ' . $root.$dir);
      }
    }
    //initialise git
    chdir($root);
    if(!file_exists('.git')) {
      $this->console->write(exec('git init'));
    }
    if(!file_exists('lib/neptune')) {
      $this->console->write('Cloning neptune...');
      $this->console->write(exec('git submodule add https://github.com/glynnforrest/neptune.git lib/neptune'));
    }

  }
}
?>

<?php

namespace Neptune\Tasks;

use Neptune\Core\Config;
use Neptune\Helpers\String;
use Neptune\Tasks\Task;

/**
 * ConfigTask
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigTask extends Task {

	protected $neptune_settings = array(
		'namespace' => '',
	);

	protected $config_settings = array(
		'view.dir' => 'app/:namespace/View',
		'root_url' => ''
	);


	protected function getRootDir() {
		$root = Config::load('neptune')->getRequired('dir.root');
		//make sure root has a trailing slash
		if(substr($root, -1) !== '/') {
			$root .= '/';
		}
		return $root;
	}

	/**
	 * Check if configuration profiles have been setup for this
	 * application. If configuration profiles existthey will be
	 * returned as an array of profiles. This function will return
	 * false if the neptune cli config hasn't been setup.
	 */
	public function _neptuneConfigSetup() {
		$root = $this->getRootDir();
		if(!file_exists($root . 'config/neptune.php')) {
			return false;
		}
        $c = Config::load();
		//check to see if config settings required for neptune have been set
        return $c->get('namespace', false);
	}

    protected function directoriesCreated() {
        $t = new SetupTask();
        return $t->_directoriesCreated();
    }

    protected function getConfigProfiles() {
        $d = new \DirectoryIterator($this->getRootDir() . 'config/');
        $profiles = array();
		foreach($d as $file) {
			if(!$file->isDot() && $file->getFilename() !== 'neptune.php') {
                $profiles[] = $file->getFilename();
            }
		}
		return $profiles;
    }



	/**
	 * Set up configuration for your application.
	 */
	public function run() {
        if(!$this->directoriesCreated()) {
            return $this->console->write('Please run `neptune setup`.');
        }
        $file = 'config/neptune.php';
		if(!$this->_neptuneConfigSetup()) {
			$this->console->write('Set up your application');
			$c = Config::load('neptune', $file);
            $pieces = explode('/', trim($this->getRootDir(), '/'));
            $this->neptune_settings['namespace'] = String::camelCase(array_pop($pieces), true);
			foreach ($this->neptune_settings as $setting => $default) {
				$c->set($setting, $this->console->read("$setting:", $default));
			}
			$c->save();
		}
        $profiles = $this->getConfigProfiles();
        if(empty($profiles)) {
            return $this->create();
        }
        $this->console->write('The following configuration profiles exist:');
        foreach($profiles as $profile) {
            $this->console->write($profile);
        }
        if($this->console->readYesNo('Create new?')) {
            return $this->create();
        }
        return true;
	}

    public function create($name = null) {
        if(!$name) {
            $name = $this->console->read('Configuration name', 'development');
        }
        $filename = $this->getRootDir() . 'config/' . String::slugify($name) . '.php';
        if(file_exists($filename)) {
            return $this->console->error('File exists: ' . $filename);
        }
        $this->console->write('Creating ' . $filename);
        $c = Config::create($name, $filename);
		$namespace = Config::load('neptune')->getRequired('namespace');
		foreach ($this->config_settings as $setting => $default) {
			$default = str_replace(':namespace', $namespace, $default);
			$c->set($setting, $this->console->read("$setting:", $default));
		}
		$c->save();
		$this->console->write("Saved $filename");
    }


}

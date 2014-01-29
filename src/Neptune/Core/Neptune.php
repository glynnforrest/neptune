<?php

namespace Neptune\Core;

use Neptune\Exceptions\NeptuneError;
use Neptune\Core\Events;
use Neptune\Core\ComponentException;

class Neptune {

    protected $config;
	protected $env;

	public function __construct(Config $config) {
        $this->config = $config;
	}

	/**
	 * Load the environment $env. This will include the file
	 * app/env/$env.php and call Config::loadEnv($env). If $env is not
	 * defined, the value of the config key 'env' in
	 * config/neptune.php will be used.
	 */
	public function loadEnv($env = null) {
		$c = Config::load('neptune');
		if(!$env) {
			$env = $c->getRequired('env');
		}
		$this->env = $env;
		include $c->getRequired('dir.root') . 'app/env/' . $env . '.php';
		Config::loadEnv($env);
		return true;
	}

	/**
	 * Get the name of the currently loaded environment.
	 */
	public function getEnv() {
		return $this->env;
	}

    /**
     * Get the absolute path of the root directory of the
     * application.
     *
     * @return string The root directory, with a trailing slash.
     */
    public function getRootDirectory()
    {
		$root = $this->config->getRequired('dir.root');
		//make sure root has a trailing slash
		if(substr($root, -1) !== '/') {
			$root .= '/';
		}

		return $root;
    }

	/**
	 * Get the absolute path of a module directory.
	 *
	 * @param string $module The name of the module
	 */
	public function getModuleDirectory($module)
    {
		return $this->config->getPath('modules.' . $module);
	}

	/**
	 * Get the namespace of a module with no beginning slash.
	 *
	 * @param string $module the name of the module
	 */
	public function getModuleNamespace($module)
    {
		$namespace = Config::load($module)->getRequired('namespace');
		if(substr($namespace, 0, 1) === '\\') {
			$namespace = substr($namespace, 0, 1);
		}
		return $namespace;
	}

    public function getDefaultModule()
    {
		$modules = $this->config->get('modules');
		if(!$modules) {
			return null;
		}
		$module_names = array_keys($modules);
		return $module_names[0];
    }

	public function handleErrors() {
		set_error_handler('\Neptune\Core\Neptune::dealWithError');
	}

	public static function dealWithError($errno, $errstr, $errfile, $errline, $errcontext) {
		throw new NeptuneError($errno, $errstr, $errfile, $errline, $errcontext);
	}

}

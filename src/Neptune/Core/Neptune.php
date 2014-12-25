<?php

namespace Neptune\Core;

use Neptune\Exceptions\NeptuneError;
use Neptune\Core\Events;
use Neptune\Service\ServiceInterface;
use Neptune\Service\AbstractModule;
use Neptune\Core\ComponentException;
use Neptune\EventListener\StringResponseListener;
use Neptune\Config\Config;
use Neptune\Config\ConfigManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

use Pimple\Container;

class Neptune extends Container implements HttpKernelInterface, TerminableInterface
{
    const NEPTUNE_VERSION = '0.5-dev';

    protected $env;
    protected $booted;
    protected $services = array();
    protected $modules = array();
    protected $root_directory;

    public function __construct($root_directory)
    {
        //init Pimple
        parent::__construct();

        //make sure root has a trailing slash
        if (substr($root_directory, -1) !== '/') {
            $root_directory .= '/';
        }
        $this->root_directory = $root_directory;

        $this['config'] = function() {
            $config = new Config('neptune', $this->root_directory . 'config/neptune.php');
            $config->setRootDirectory($this->root_directory);
            return $config;
        };

        $this['config.manager'] = function() {
            $manager = new ConfigManager($this);
            $manager->add($this['config']);
            return $manager;
        };

        $this['dispatcher'] = function () {
            return new EventDispatcher;
        };

        $this['request_stack'] = function () {
            return new RequestStack();
        };

        $this['kernel'] = function() {
            return new HttpKernel($this['dispatcher'], $this['resolver'], $this['request_stack']);
        };
    }

    /**
     * Register a service.
     *
     * @param ServiceInterface The service to add
     */
    public function addService(ServiceInterface $service)
    {
        $this->services[] = $service;
        return $service->register($this);
    }

    /**
     * Register a module.
     *
     * @param AbstractModule The module to add
     */
    public function addModule(AbstractModule $module)
    {
        $this->addService($module);
        $this->modules[$module->getName()] = $module;
    }

    /**
     * Get a registered module.
     *
     * @param $name The name of the registered module
     * @return AbstractModule The module
     */
    public function getModule($name)
    {
        if (!isset($this->modules[$name])) {
            throw new \InvalidArgumentException(sprintf('Module "%s" not registered', $name));
        }

        return $this->modules[$name];
    }

    /**
     * Get all registered modules as an associative array of names and
     * instances.
     *
     * @return array An array of registered modules.
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Boot up the application services. This is called automatically
     * by handle().
     */
    public function boot()
    {
        if(!$this->booted) {
            $dispatcher = $this['dispatcher'];
            $dispatcher->addSubscriber(new StringResponseListener());

            foreach ($this->services as $service) {
                $service->boot($this);
            }
        }
        $this->booted = true;
        return true;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $this->boot();

        return $this['kernel']->handle($request, $type, $catch);
    }

    public function go()
    {
        $request = Request::createFromGlobals();
        $response = $this->handle($request);
        $response->send();
        $this->terminate($request, $response);
    }

    public function terminate(Request $request, Response $response)
    {
        $this['kernel']->terminate($request, $response);
    }

	/**
	 * Load the environment config $env. If $env is not
	 * defined, the value of the config key 'env' in
	 * config/neptune.php will be used.
	 */
	public function loadEnv($env = null) {
		if(!$env) {
			$env = $this['config']->getRequired('env');
		}
        $file = $this->root_directory . 'config/env/' . $env . '.php';
        //load $env as a config file, merging into neptune
        $this['config.manager']->load($env, $file, 'neptune');
		$this->env = $env;

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
        return $this->root_directory;
    }

	/**
	 * Get the absolute path of a module directory.
	 *
	 * @param string $module The name of the module
	 */
	public function getModuleDirectory($module)
    {
		return $this->getModule($module)->getDirectory();
	}

    /**
     * Get the namespace of a module with no beginning slash.
     *
     * @param string $module the name of the module
     */
    public function getModuleNamespace($module)
    {
        return $this->getModule($module)->getNamespace();
    }

    /**
     * Get the name of the first registered module.
     *
     * @return string The name of the module
     */
    public function getDefaultModule()
    {
        if (empty($this->modules)) {
            throw new \Exception('Unable to get default module, none registered');
        }
        reset($this->modules);

        return key($this->modules);
    }

	public function handleErrors() {
		set_error_handler('\Neptune\Core\Neptune::dealWithError');
	}

	public static function dealWithError($errno, $errstr, $errfile, $errline, $errcontext) {
		throw new NeptuneError($errno, $errstr, $errfile, $errline, $errcontext);
	}

}

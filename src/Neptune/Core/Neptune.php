<?php

namespace Neptune\Core;

use Neptune\Exceptions\NeptuneError;
use Neptune\Core\Events;
use Neptune\Service\ServiceInterface;
use Neptune\Service\AbstractModule;
use Neptune\Core\ComponentException;
use Neptune\Routing\Router;
use Neptune\Routing\ControllerResolver;
use Neptune\EventListener\RouterListener;
use Neptune\EventListener\StringResponseListener;
use Neptune\Config\Config;
use Neptune\Config\ConfigManager;
use Neptune\Helpers\Url;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\EventDispatcher\EventDispatcher;

use \Pimple;

class Neptune extends Pimple implements HttpKernelInterface
{

    protected $env;
    protected $booted;
    protected $services = array();
    protected $modules = array();
    protected $module_routes = array();
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

        $this['url'] = function() {
            return new Url($this['config']->getRequired('root_url'));
        };

        $this['router'] = function() {
            return new Router($this['url']);
        };

        $this['dispatcher'] = function () {
            return new EventDispatcher;
        };

        $this['resolver'] = function () {
            return new ControllerResolver($this);
        };

        $this['request_stack'] = function () {
            return new RequestStack();
        };
    }

    /**
     * Add a service to this Neptune instance.
     *
     * @param ServiceInterface The service to add.
     */
    public function addService(ServiceInterface $service)
    {
        $this->services[] = $service;
        return $service->register($this);
    }

    /**
     * Add a module to this Neptune instance. If $route_prefix is a
     * string, the module will be routed using the string as a prefix.
     * If true, the module will be routed without a prefix. If false,
     * the module will not be routed.
     *
     * @param AbstractModule The module to add
     * @param mixed $route_prefix The prefix to use in routes for the module
     */
    public function addModule(AbstractModule $module, $route_prefix = false)
    {
        $this->addService($module);
        $name = $module->getName();
        $this->modules[$name] = $module;
        if ($route_prefix) {
            $this->module_routes[$name] = $route_prefix;
        }
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
     * Get the route prefix of a registered module. False will be
     * returned if the module isn't registered or routing is disabled
     * for that module.
     *
     * @param string $module The name of the module
     * @return string The route prefix, or false on failure.
     */
    public function getRoutePrefix($module)
    {
        return isset($this->module_routes[$module]) ? $this->module_routes[$module] : false;
    }

    /**
     * Boot up the application services. This is called automatically
     * by handle().
     */
    public function boot()
    {
        if(!$this->booted) {
            $dispatcher = $this['dispatcher'];
            $dispatcher->addSubscriber(new RouterListener($this['router'], $this));
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
        $kernel = new HttpKernel($this['dispatcher'], $this['resolver'], $this['request_stack']);
        return $kernel->handle($request, $type, $catch);
    }

    public function go()
    {
        $response = $this->handle(Request::createFromGlobals());
        $response->send();
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

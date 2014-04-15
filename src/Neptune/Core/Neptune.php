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

    public function __construct(Config $config)
    {
        parent::__construct();
        $this['config'] = $config;

        $this['router'] = new Router($config);

        $this['dispatcher'] = function () {
            $dispatcher = new EventDispatcher;
            $dispatcher->addSubscriber(new RouterListener($this['router']));
            $dispatcher->addSubscriber(new StringResponseListener());
            return $dispatcher;
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

    public function addModule($name, AbstractModule $module)
    {
        $this->addService($module);
        $this->modules[$name] = $module;
    }

    public function getModule($name)
    {
        if (!isset($this->modules[$name])) {
            throw new \InvalidArgumentException(sprintf('Module "%s" not registered', $name));
        }

        return $this->modules[$name];
    }

    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Boot up the application. This is called automatically by
     * handle().
     */
    public function boot()
    {
        if(!$this->booted) {
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

	/**
	 * Load the environment $env. This will include the file
	 * app/env/$env.php and call Config::loadEnv($env). If $env is not
	 * defined, the value of the config key 'env' in
	 * config/neptune.php will be used.
	 */
	public function loadEnv($env = null) {
		if(!$env) {
			$env = $this['config']->getRequired('env');
		}
		$this->env = $env;
		Config::loadEnv($env);
		$file = $this['config']->getRequired('dir.root') . 'app/env/' . $env . '.php';
        if(file_exists($file)) {
            include $file;
        }
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
        $root = $this['config']->getRequired('dir.root');
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

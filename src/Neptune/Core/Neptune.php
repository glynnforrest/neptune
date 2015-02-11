<?php

namespace Neptune\Core;

use Neptune\Exceptions\NeptuneError;
use Neptune\Core\Events;
use Neptune\Service\ServiceInterface;
use Neptune\Service\AbstractModule;
use Neptune\Core\ComponentException;
use Neptune\EventListener\StringResponseListener;
use Neptune\Config\Config;
use Neptune\Config\Loader;
use Neptune\Config\Processor;
use Neptune\Config\ConfigManager;
use Neptune\Config\ConfigCache;
use Neptune\Config\Exception\ConfigFileException;

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
    protected $env_locked;
    protected $booted;
    protected $services = array();
    protected $modules = array();
    protected $root_directory;
    protected $cache_enabled;

    public function __construct($root_directory)
    {
        //init Pimple
        parent::__construct();

        //make sure root has a trailing slash
        if (substr($root_directory, -1) !== '/') {
            $root_directory .= '/';
        }
        $this->root_directory = $root_directory;

        $this['config.cache'] = function() {
            $cache_file = $this->root_directory.'storage/cache/config-'.$this->env.'.php';

            return new ConfigCache($cache_file);
        };

        $this['config'] = function() {
            $this->env_locked = true;

            if ($this->cache_enabled) {
                $cache = $this['config.cache'];
                if ($cache->isSaved()) {
                    return $cache->getConfig();
                }
            }

            $manager = $this['config.manager'];

            //load configuration for each module
            foreach ($this->modules as $module) {
                $module->loadConfig($manager);
            }

            //then for the application (default is config/neptune.yml).
            $this->loadConfig($manager);

            $config = $manager->getConfig();
            if ($this->cache_enabled) {
                $cache->save($config, $manager->getCacheMessage());
            }

            return $config;
        };

        $this['config.manager'] = function($neptune) {
            $manager = new ConfigManager(new Config);

            $manager->addLoader(new Loader\YamlLoader());
            $manager->addLoader(new Loader\PhpLoader());

            $manager->addProcessor(new Processor\OptionsProcessor());
            $manager->addProcessor(new Processor\EnvironmentProcessor($neptune));
            $manager->addProcessor(new Processor\ReferenceProcessor());

            return $manager;
        };

        $this['dispatcher'] = function () {
            $dispatcher = new EventDispatcher();

            foreach ($this->getTaggedServices('neptune.dispatcher.subscribers') as $subscriber) {
                $dispatcher->addSubscriber($subscriber);
            }

            return $dispatcher;
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
        $service->register($this);
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
     * Get a collection of services whose names are referenced in a
     * configuration key.
     *
     * @param string $tag            The configuration key
     * @param string $config_service The config service containing the key
     */
    public function getTaggedServices($tag, $config_service = 'config')
    {
        $services = [];
        $service_names = (array) $this[$config_service]->get($tag, []);

        foreach ($service_names as $service) {
            $services[] = $this->offsetGet($service);
        }

        return $services;
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
     * Set the environment name to use when loading configuration for
     * this application. An exception will be thrown if configuration
     * has already been loaded.
     *
     * @param string $env The environment name
     */
    public function setEnv($env)
    {
        if ($this->env_locked) {
            $locked = $this->env ? sprintf('locked to "%s"', $this->env) : 'locked';
            throw new \Exception(sprintf('Environment is %s because configuration is already loaded.', $locked));
        }

        $this->env = $env;
    }

    /**
     * Enable caching of configuration for faster performance. Any
     * configuration changes will not take affect until the cache is
     * cleared.
     *
     * @param bool $enabled
     */
    public function enableCache($enabled = true)
    {
        if ($this->env_locked) {
            $status = $this->cache_enabled ? 'enabled' : 'disabled';
            throw new \Exception(sprintf('Application cache is locked to "%s" because configuration is already loaded.', $status));
        }

        $this->cache_enabled = $enabled;
    }

    /**
     * Get the environment name of this application.
     *
     * @return string The name of the environment
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * Load configuration specific to this application.
     *
     * @param ConfigManager $config
     */
    public function loadConfig(ConfigManager $config)
    {
        $config->load($this->root_directory.'config/neptune.yml');

        if ($this->env) {
            $config->load(sprintf('%sconfig/env/%s.yml', $this->root_directory, $this->env));
        }
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

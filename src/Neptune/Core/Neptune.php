<?php

namespace Neptune\Core;

use Neptune\Exceptions\NeptuneError;
use Neptune\Core\Events;
use Neptune\Core\ComponentException;
use Neptune\Routing\Router;
use Neptune\Routing\ControllerResolver;
use Neptune\EventListener\RouterListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\EventDispatcher\EventDispatcher;

use \Pimple;

class Neptune extends Pimple implements HttpKernelInterface
{

    protected $env;

    public function __construct(Config $config)
    {
        $this['config'] = $config;

        $this['router'] = new Router($config);

        $this['dispatcher'] = $this->share(function () {
            $dispatcher = new EventDispatcher;
            $dispatcher->addSubscriber(new RouterListener($this['router']));
            return $dispatcher;
        });

        $this['resolver'] = $this->share(function() {
            return new ControllerResolver($this);
        });

        $this['request_stack'] = $this->share(function () {
            return new RequestStack();
        });
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
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
		include $this['config']->getRequired('dir.root') . 'app/env/' . $env . '.php';
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
		return $this['config']->getPath('modules.' . $module);
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
		$modules = $this['config']->get('modules');
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

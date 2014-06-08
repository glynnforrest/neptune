<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Config\Config;
use Neptune\Security\SecurityFactory;
use Neptune\Security\SecurityRequestListener;

use Blockade\Firewall;
use Blockade\CsrfManager;
use Blockade\EventListener\FirewallListener;
use Blockade\EventListener\BlockadeExceptionListener;

use Reform\EventListener\CsrfListener;
use Symfony\Component\HttpFoundation\RequestMatcher;

/**
 * SecurityService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SecurityService implements ServiceInterface
{

    protected $config;

    public function __construct(Config $config = null)
    {
        $this->config = $config;
    }

    public function register(Neptune $neptune)
    {
        //if no config was supplied, grab the default
        if (!$this->config) {
            $config = $neptune['config'];
        }

        $neptune['security'] = function () use ($neptune, $config) {
            return new SecurityFactory($config, $neptune);
        };

        //register firewalls if defined
        if ($config->get('security.firewalls', false)) {
            $this->registerFirewall($neptune, $config);
        }

        //register csrf by default
        if ($config->get('security.csrf', true)) {
            $this->registerCsrf($neptune, $config);
        }

        $neptune['security.resolver'] = function () {
            return new BlockadeExceptionListener();
            //add resolvers automatically
        };

        $neptune['security.request'] = function ($neptune) {
            return new SecurityRequestListener($neptune['security']);
        };
    }

    protected function registerFirewall(Neptune $neptune, Config $config)
    {
        $neptune['security.firewall'] = function () use ($neptune, $config) {
            $listener = new FirewallListener();
            foreach ($config->get('security.firewalls', array()) as $name => $firewall) {
                $listener->addFirewall($this->createFirewall($neptune, $config, $name));
            }

            return $listener;
        };
    }

    protected function createFirewall(Neptune $neptune, Config $config, $name)
    {
        $driver_key = $config->get("security.firewalls.$name.driver");
        $driver = $neptune['security']->get($driver_key);
        $firewall = new Firewall($name, $driver);

        // register rules
        $rules = $config->get("security.firewalls.$name.rules", array());
        foreach ($rules as $rule => $permission) {
            $firewall->addRule(new RequestMatcher($rule), $permission);
        }

        // register exemptions
        $exemptions = $config->get("security.firewalls.$name.exemptions", array());
        foreach ($exemptions as $exemption => $permission) {
            $firewall->addExemption(new RequestMatcher($exemption), $permission);
        }

        return $firewall;
    }

    protected function registerCsrf(Neptune $neptune, Config $config)
    {
        $neptune['security.csrf'] = function () use ($neptune, $config) {
            //get the session and form tokens
            $session_token = $config->get('security.csrf.session_token', 'security.csrf.token');
            $form_token = $config->get('security.csrf.form_token', '_token');

            $manager = new CsrfManager($neptune['session'], $session_token);

            return new CsrfListener($manager, $form_token);
        };
    }

    public function boot(Neptune $neptune)
    {
        $dispatcher = $neptune['dispatcher'];

        $dispatcher->addSubscriber($neptune['security.resolver']);
        $dispatcher->addSubscriber($neptune['security.request']);

        //add the firewall if set up
        if ($neptune->offsetExists('security.firewall')) {
            $neptune['dispatcher']->addSubscriber($neptune['security.firewall']);
        }

        //add csrf if set up
        if ($neptune->offsetExists('security.csrf')) {
            $neptune['dispatcher']->addSubscriber($neptune['security.csrf']);
        }
    }

}

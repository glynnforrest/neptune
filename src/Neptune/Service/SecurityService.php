<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Config\Config;
use Neptune\Security\SecurityFactory;
use Neptune\Security\SecurityRequestListener;
use Neptune\View\Extension\SecurityExtension;
use Blockade\Firewall;
use Blockade\EventListener\FirewallListener;
use Blockade\EventListener\BlockadeExceptionListener;
use Symfony\Component\HttpFoundation\RequestMatcher;

/**
 * SecurityService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SecurityService implements ServiceInterface
{
    public function register(Neptune $neptune)
    {
        $neptune['security'] = function ($neptune) {
            return new SecurityFactory($neptune['config'], $neptune);
        };

        $neptune['security.firewall'] = function ($neptune) {
            $listener = new FirewallListener();
            $config = $neptune['config'];

            foreach ($config->get('neptune.security.firewalls', []) as $name => $firewall) {
                $listener->addFirewall($this->createFirewall($neptune, $config, $name));
            }

            return $listener;
        };

        $neptune['security.resolver'] = function () {
            return new BlockadeExceptionListener();
            //add resolvers automatically
        };

        $neptune['security.request'] = function ($neptune) {
            return new SecurityRequestListener($neptune['security']);
        };

        $neptune['view.extension.security'] = function ($neptune) {
            return new SecurityExtension($neptune['security']);
        };
    }

    protected function createFirewall(Neptune $neptune, Config $config, $name)
    {
        $driver_key = $config->get("neptune.security.firewalls.$name.driver");
        $driver = $neptune['security']->get($driver_key);
        $firewall = new Firewall($name, $driver);

        // register rules
        $rules = $config->get("neptune.security.firewalls.$name.rules", array());
        foreach ($rules as $rule => $permission) {
            $firewall->addRule(new RequestMatcher($rule), $permission);
        }

        // register exemptions
        $exemptions = $config->get("neptune.security.firewalls.$name.exemptions", array());
        foreach ($exemptions as $exemption => $permission) {
            $firewall->addExemption(new RequestMatcher($exemption), $permission);
        }

        return $firewall;
    }

    public function boot(Neptune $neptune)
    {
        $dispatcher = $neptune['dispatcher'];

        $dispatcher->addSubscriber($neptune['security.resolver']);
        $dispatcher->addSubscriber($neptune['security.request']);
        $dispatcher->addSubscriber($neptune['security.firewall']);
    }
}

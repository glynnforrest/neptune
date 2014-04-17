<?php

namespace Neptune\Security;

use Neptune\Security\Driver\SecurityDriverInterface;
use Neptune\Security\Driver\PassDriver;
use Neptune\Security\Driver\FailDriver;
use Neptune\Security\Driver\ConfigDriver;

use Neptune\Core\AbstractFactory;
use Neptune\Exceptions\ConfigKeyException;
use Neptune\Exceptions\DriverNotFoundException;

/**
 * SecurityFactory
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class SecurityFactory extends AbstractFactory
{

    protected function create($name = null)
    {
        if (!$name) {
            $names = array_keys($this->config->get('security.drivers', array()));
            if (empty($names)) {
                throw new ConfigKeyException(
                    'Security drivers configuration array is empty');
            }
            $name = $names[0];
        }
        //if the entry in the config is a string, load it as a service
        $maybe_service = $this->config->getRequired("security.drivers.$name");
        if (is_string($maybe_service)) {
            //check the service implements security interface first
            $service = $this->neptune[$maybe_service];
            if ($service instanceof SecurityDriverInterface) {
                return $service;
            }
            throw new DriverNotFoundException(sprintf(
                "Security driver '%s' requested service '%s' which does not implement Neptune\Security\Driver\SecurityDriverInterface",
                $name,
                $maybe_service));
        }

        $driver = $this->config->getRequired("security.drivers.$name.driver");

        $method = 'create' . ucfirst($driver) . 'Driver';
        if (method_exists($this, $method)) {
            $this->instances[$name] = $this->$method($name);

            return $this->instances[$name];
        }
        throw new DriverNotFoundException("Security driver not implemented: $driver");
    }

    public function createPassDriver($name)
    {
        return new PassDriver();
    }

    public function createFailDriver($name)
    {
        return new FailDriver();
    }

    public function createConfigDriver($name)
    {
        $user_key = "security.drivers.$name.user";
        $pass_key = "security.drivers.$name.pass";

        return new ConfigDriver($this->config, $user_key, $pass_key);
    }

}

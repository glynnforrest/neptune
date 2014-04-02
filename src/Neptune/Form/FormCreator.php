<?php

namespace Neptune\Form;

use Reform\Form\Form;
use Neptune\Core\Neptune;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * FormCreator
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FormCreator
{

    protected $neptune;
    protected $dispatcher;
    protected $registered = array();

    public function __construct(Neptune $neptune, EventDispatcherInterface $dispatcher)
    {
        $this->neptune = $neptune;
        $this->dispatcher = $dispatcher;
    }

    public function register($name, $class)
    {
        $this->registered[$name] = $class;
    }

    public function create($name = null, $action = null)
    {
        if (!$name) {
            return new Form($this->dispatcher, $action);
        }

        if (!isset($this->registered[$name])) {
            throw new \RuntimeException(sprintf('Form "%s" is not registered', $name));
        }

        $form = $this->registered[$name];

        //the form may be a service - check for ::
        if (substr($form, 0, 2) === '::') {
            $service = substr($form, 2);
            $function = $this->neptune->raw($service);
            return $function($this->dispatcher, $action);
        }

        //check for service and resolve from neptune
        return new $form($this->dispatcher, $action);
    }

}

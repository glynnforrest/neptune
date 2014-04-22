<?php

namespace Neptune\Form;

use Reform\Form\Form;
use Neptune\Core\Neptune;

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
        $form = $this->doCreate($name, $action);
        $form->setEventDispatcher($this->dispatcher);

        return $form;
    }

    protected function doCreate($name, $action)
    {
        if (!$name) {
            return new Form($action);
        }

        if (!isset($this->registered[$name])) {
            throw new \RuntimeException(sprintf('Form "%s" is not registered', $name));
        }

        $form = $this->registered[$name];

        //the form may be a service - check for ::
        //if not, assume a class name
        if (substr($form, 0, 2) !== '::') {
            return new $form($action);
        }

        $service = substr($form, 2);
        $function = $this->neptune->raw($service);
        return $function($action);
    }

}

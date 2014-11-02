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

    public function __construct(Neptune $neptune, EventDispatcherInterface $dispatcher)
    {
        $this->neptune = $neptune;
        $this->dispatcher = $dispatcher;
    }

    public function create($name = null, $action = null)
    {
        $form = $this->doCreate($name, $action);
        if (!$form instanceof Form) {
            throw new \RuntimeException(sprintf('Service "%s" is not an instance of Reform\Form\Form', $name));
        }
        $form->setEventDispatcher($this->dispatcher);

        return $form;
    }

    protected function doCreate($name, $action)
    {
        if (!$name) {
            return new Form($action);
        }

        if (substr_count($name, ':') !== 1) {
            return $this->neptune[$name];
        }

        $pos = strpos($name, ':' );
        $module = $this->neptune->getModule(substr($name, 0, $pos));
        $form = substr($name, $pos + 1);
        $form_class = $module->getNamespace() . '\\Form\\' . ucfirst($form) . 'Form';

        return new $form_class($action);
    }

}

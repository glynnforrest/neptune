<?php

namespace Neptune\Controller;

use Neptune\Assets\Assets;
use Neptune\Core\Neptune;
use Neptune\Core\NeptuneAwareInterface;
use Reform\Form\Form;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller
 * @author Glynn Forrest me@glynnforrest.com
 */
abstract class Controller implements NeptuneAwareInterface
{

    protected $neptune;

    public function setNeptune(Neptune $neptune)
    {
        $this->neptune = $neptune;
    }

    public function getNeptune()
    {
        return $this->neptune;
    }

    public function assets()
    {
        return $this->neptune['assets'];
    }

    public function security(Request $request, $driver = null)
    {
        if (!$this->neptune->offsetExists('security')) {
            throw new \Exception('Security service has not been registered');
        }

        $security = $this->neptune['security']->get($driver);
        $security->setRequest($request);
        return $security;
    }

    public function database()
    {
        //return database service
    }

    public function view($view, array $values = array())
    {
        if (!$this->neptune->offsetExists('view')) {
            throw new \Exception('View service has not been registered');
        }

        return $this->neptune['view']->load($view, $values);
    }

    public function form($name = null, $action = null)
    {
        return $this->neptune['form']->create($name, $action);
    }

    public function redirect($to, $with = array())
    {
        //set session parameters here
        return new RedirectResponse($to);
    }

}

<?php

namespace Neptune\Controller;

use Neptune\Assets\Assets;
use Neptune\Core\Neptune;
use Neptune\Core\NeptuneAwareInterface;
use Neptune\Core\RequestAwareInterface;
use Neptune\Form\Form;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller
 * @author Glynn Forrest me@glynnforrest.com
 */
abstract class Controller implements NeptuneAwareInterface, RequestAwareInterface
{

    protected $neptune;
    protected $request;

    public function setNeptune(Neptune $neptune)
    {
        $this->neptune = $neptune;
    }

    public function getNeptune()
    {
        return $this->neptune;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function assets()
    {
        return Assets::getInstance();
    }

    public function isPost()
    {
        return $this->request->getMethod() === 'POST';
    }

    public function security($driver = null)
    {
        if (!$this->neptune->offsetExists('security')) {
            throw new \Exception('Security service has not been registered');
        }

        $security = $this->neptune['security'];
        $security->setRequest($this->request);
        return $security;
    }

    public function database()
    {
        //return database service
    }

    public function form($action = null)
    {
        if (!$action) {
            $action = $this->request->getUri();
        }

        return new Form($action);
    }

    public function redirect($to, $with = array())
    {
        //set session parameters here
        return new RedirectResponse($to);
    }

}

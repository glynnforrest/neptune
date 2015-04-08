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

    /**
     * Add a message to the request flash bag.
     *
     * @param Request $request
     * @param string  $key
     * @param string  $value
     */
    public function flash(Request $request, $key, $value)
    {
        $request->getSession()->getFlashbag()->add($key, $value);

        return $this;
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

    /**
     * Render a twig template.
     */
    public function render($template, array $values = [])
    {
        return $this->neptune['twig']->render($template, $values);
    }

    public function form($name = null, $action = null)
    {
        return $this->neptune['form']->create($name, $action);
    }

    /**
     * Create a new redirect response.
     *
     * @param string $to The url to redirect to
     *
     * @return RedirectResponse
     */
    public function redirect($to)
    {
        return new RedirectResponse($to);
    }

    /**
     * Create a new redirect response to a named route.
     *
     * @param string $route_name The route to redirect to
     * @param array  $params     The parameters in the url
     *
     * @return RedirectResponse
     */
    public function redirectTo($route_name, array $params = [])
    {
        $url = $this->neptune['router']->url($route_name, $params);

        return $this->redirect($url);
    }
}

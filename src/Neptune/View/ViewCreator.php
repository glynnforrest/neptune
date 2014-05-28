<?php

namespace Neptune\View;

use Neptune\Core\Neptune;
use Neptune\View\Extension\ExtensionInterface;

/**
 * ViewCreator
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ViewCreator
{

    /**
     * @var Neptune
     */
    protected $neptune;
    protected $helpers = array();

    public function __construct(Neptune $neptune)
    {
        $this->neptune = $neptune;
    }

    public function load($view, array $values = array())
    {
        $pos = strpos($view, ':');
        if ($pos) {
            $module = substr($view, 0, $pos);
            $view = substr($view, $pos + 1);
        } else {
            $module = $this->neptune->getDefaultModule();
        }
        $template = sprintf('%sviews/%s.php', $this->neptune->getModuleDirectory($module), $view);
        $view = new View($template);
        $view->setValues($values);
        $view->setCreator($this);

        return $view;
    }

    /**
     * Add a function as a helper to use inside of views.
     *
     * @param  string      $name     The name of the helper
     * @param  callable    $function The function
     * @return ViewCreator This ViewCreator
     */
    public function addHelper($name, $function)
    {
        $this->helpers[$name] = $function;

        return $this;
    }

    /**
     * Get an array of all helper functions.
     *
     * @return array An array of callable helpers
     */
    public function getHelpers()
    {
        return $this->helpers;
    }

    /**
     * Call a registered view helper.
     *
     * @param  string $name The name of the helper
     * @param  array  $args The arguments to the function
     * @return mixed  The return value from the helper function
     */
    public function callHelper($name, array $args)
    {
        return call_user_func_array($this->helpers[$name], $args);
    }

    public function addExtension(ExtensionInterface $extension)
    {
        //grab all the helpers from the extension and add to the
        //helpers array
        $helpers = (array) $extension->getHelpers();
        foreach ($helpers as $name => $method) {
            $this->helpers[$name] = array($extension, $method);
        }

        return $this;
    }
}

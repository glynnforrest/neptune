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

    protected function getViewFilename($view)
    {
        $pos = strpos($view, ':');
        if (!$pos) {
            //no module has been supplied, so use the app directory
            return sprintf('%sapp/views/%s.php', $this->neptune->getRootDirectory(), $view);
        }

        //the template is in a module
        $module = substr($view, 0, $pos);
        $view = substr($view, $pos + 1);

        //check for an overriding template in the app/ folder
        $override = sprintf('%sapp/views/%s/%s.php', $this->neptune->getRootDirectory(), $module, $view);
        if (file_exists($override)) {
            return $override;
        }

        return sprintf('%sviews/%s.php', $this->neptune->getModuleDirectory($module), $view);
    }

    /**
     * Load a view template. The view may be of the form <view> or
     * <module>:<view>. A template inside a module is by overridden by
     * a template with the same name inside the app/views/<module>
     * directory.
     *
     * @param  string $view The name of the view template
     * @return View   The View instance
     */
    public function load($view, array $values = array())
    {
        $view = new View($this->getViewFilename($view));
        $view->setValues($values);
        $view->setCreator($this);

        return $view;
    }

    /**
     * Check if a view template is available. The view may be of the
     * form <view> or <module>:<view>.
     *
     * @param  string $view The name of the view template
     * @return bool   If the template is available or not
     */
    public function has($view)
    {
        try {
            $template = $this->getViewFilename($view);

            return file_exists($template);
        } catch (\Exception $e) {
            //perhaps loading a module failed, but all we care about
            //is the existence of the template and whether we can
            //access it
            return false;
        }
    }

    /**
     * Add a function as a helper to use inside of templates.
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
        if (!isset($this->helpers[$name])) {
            throw new \Exception("View helper not found: $name");
        }

        return call_user_func_array($this->helpers[$name], $args);
    }

    /**
     * Add an Extension to use inside of templates.
     *
     * @param  ExtensionInterface $extension The Extension
     * @return ViewCreator        This ViewCreator
     */
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

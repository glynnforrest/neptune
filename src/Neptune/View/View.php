<?php

namespace Neptune\View;

use Neptune\Exceptions\ViewNotFoundException;

class View
{
    protected $vars = [];
    protected $view;
    protected $creator;

    public function __construct($view, array $vars = [])
    {
        $this->view = $view;
        $this->vars = $vars;
    }

    /**
     * Set the ViewCreator instance that created this view. This will
     * allow for view extensions to be called.
     *
     * @param ViewCreator $creator
     */
    public function setCreator(ViewCreator $creator)
    {
        $this->creator = $creator;
    }

    /**
     * Get the ViewCreator instance that created this view.
     *
     * @throws \Exception
     * @return ViewCreator
     */
    public function getCreator()
    {
        if (!isset($this->creator)) {
            throw new \Exception('ViewCreator not set');
        }

        return $this->creator;
    }

    /**
     * Convenience method for set().
     *
     * @param string $key   The name of the variable
     * @param mixed  $value The value
     *
     * @return View This view instance
     */
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Set a variable.
     *
     * @param string $key   The name of the variable
     * @param mixed  $value The value
     *
     * @return View This view instance
     */
    public function set($key, $value)
    {
        $this->vars[$key] = $value;

        return $this;
    }

    /**
     * Convenience method for get().
     *
     * @param string $key The name of the variable
     *
     * @return mixed The value of the variable
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Get a variable or a default if it is not set.
     *
     * @param string $key     The name of the variable
     * @param mixed  $default The value to return if the variable is not set
     *
     * @return mixed The value of the variable
     */
    public function get($key, $default = null)
    {
        return isset($this->vars[$key]) ? $this->vars[$key] : $default;
    }

    /**
     * Check if a variable is defined.
     *
     * @param string $key The name of the variable
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->vars[$key]);
    }

    /**
     * Add an array of variables.
     *
     * @param array $values An array of keys and values
     *
     * @return View This view instance
     */
    public function addValues(array $values = [])
    {
        $this->values = array_merge($this->values, $values);

        return $this;
    }

    /**
     * Set all variables and overwrite any current variables.
     *
     * @param array $values An array of keys and values
     *
     * @return View This view instance
     */
    public function setValues(array $values = [])
    {
        $this->vars = $values;

        return $this;
    }

    /**
     * Get all variables.
     *
     * @return array An array of keys and values
     */
    public function getValues()
    {
        return $this->vars;
    }

    /**
     * Set the file path of the template.
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Get the file path of the template for this View instance.
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Render the view template with variables replaced by their
     * values.
     *
     * @return string The contents of the view template
     */
    public function render()
    {
        if (!file_exists($this->view)) {
            throw new ViewNotFoundException("View template not found: $this->view");
        }
        ob_start();
        include $this->view;

        return ob_get_clean();
    }

    /**
     * Call a named view helper.
     *
     * @param string $method The name of the helper
     * @param array  $args   The arguments to pass to the helper
     */
    public function __call($method, $args)
    {
        return $this->getCreator()->callHelper($method, $args);
    }

}

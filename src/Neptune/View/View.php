<?php

namespace Neptune\View;

use Neptune\View\Exception\ViewNotFoundException;
use Neptune\View\Exception\ViewCreatorException;

class View
{
    protected $vars = [];
    protected $pathname;
    protected $creator;

    public function __construct($pathname, array $vars = [])
    {
        $this->pathname = $pathname;
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
     * @throws ViewCreatorException
     * @return ViewCreator
     */
    public function getCreator()
    {
        if (!isset($this->creator)) {
            throw new ViewCreatorException(sprintf('ViewCreator not set on view with template "%s"', $this->pathname));
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
        $this->vars = array_merge($this->vars, $values);

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
     *
     * @param string $pathname The file path
     *
     * @return View This view instance
     */
    public function setPathname($pathname)
    {
        $this->pathname = $pathname;

        return $this;
    }

    /**
     * Get the file path of the template.
     *
     * @return string The file path
     */
    public function getPathname()
    {
        return $this->pathname;
    }

    /**
     * Render the view template with variables replaced by their
     * values.
     *
     * @return string The contents of the view template
     */
    public function render()
    {
        if (!file_exists($this->pathname)) {
            throw new ViewNotFoundException("View template not found: $this->pathname");
        }
        ob_start();
        try {
            include $this->pathname;
        } catch (\Exception $e) {
            //catch any exceptions in rendering the template and
            //rethrow outside of the output buffer
            ob_end_clean();
            throw $e;
        }

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

<?php

namespace Neptune\View;

use Neptune\Exceptions\ViewNotFoundException;

class View {

	protected $vars = array();
	//complete file path to the view template.
	protected $view;
    protected $creator;

	public function __construct($view, array $vars = array()) {
        $this->view = $view;
        $this->vars = $vars;
	}

    public function setCreator(ViewCreator $creator)
    {
        $this->creator = $creator;
    }

    public function getCreator()
    {
        if (!isset($this->creator)) {
            throw new \Exception('ViewCreator not set');
        }

        return $this->creator;
    }

	public function __set($key, $value) {
		return $this->set($key, $value);
	}

	public function set($key, $value) {
		$this->vars[$key] = $value;

        return $this;
	}

	public function __get($key) {
		return $this->get($key);
	}

    public function get($key, $default = null)
    {
        return isset($this->vars[$key]) ? $this->vars[$key] : $default;
    }

	public function __isset($key) {
		return isset($this->vars[$key]) ? true : false;
	}

	public function addValues(array $values = array()) {
        $this->values = array_merge($this->values, $values);

		return $this;
	}

    public function setValues(array $values = array())
    {
        $this->vars = $values;

        return $this;
    }

	public function getValues() {
		return $this->vars;
	}

    public function __toString()
    {
        try {
            $content = $this->render();
        } catch (\Exception $e) {
            return '';
        }
        return $content;
    }

    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

	/**
	 * Get the file path of the template for this View instance.
	 */
	public function getView() {
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

    public function load($view, array $values = array())
    {
        return $this->getCreator()->load($view, $values);
    }

    public function insert($view, array $values = array())
    {
        echo $this->load($view, $values)->render();
    }

    public function __call($method, $args)
    {
        return $this->getCreator()->callHelper($method, $args);
    }

}

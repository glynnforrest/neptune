<?php

namespace Neptune\Form;

use Neptune\Helpers\Html;
use Neptune\Http\Request;

/**
 * Form
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Form {

	protected $action;
	protected $method;
	protected $options = array();
	protected $rows = array();

	public function __construct($action = null, $method = 'post', $options = array()) {
		if(!$action) {
			$action = Request::getInstance()->uri();
		}
		$this->setHeader($action, $method, $options);
	}

	/**
	 * Set the action attribute of this Form.
	 *
	 * @param string $action The action.
	 */
	public function setAction($action) {
		$this->action = $action;
		return $this;
	}

	/**
	 * Get the action attribute of this Form.
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * Set the method attribute of this Form. An exception will be
	 * throw if $method is not an allowed http method.
	 *
	 * @param string $method The method.
	 */
	public function setMethod($method) {
		$method = strtoupper($method);
		if($method !== 'POST' && $method !== 'GET') {
			throw new \Exception("Invalid method passed to Form::setMethod: $method");
		}
		$this->method = $method;
		return $this;
	}

	/**
	 * Get the method attribute of this Form.
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * Set the options of this Form, such as class or id.
	 *
	 * @param array $options The options.
	 */
	public function setOptions(array $options = array()) {
		$this->options = $options;
		return $this;
	}

	/**
	 * Get the options attribute of this Form, such as class or id.
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * Set the action, method and any additional options of the Form.
	 *
	 * @param string $action The action.
	 * @param string $method The method.
	 * @param array $options The options.
	 */
	public function setHeader($action, $method = 'POST', array $options = array()) {
		$this->setAction($action);
		$this->setMethod($method);
		$this->setOptions($options);
		return $this;
	}

	/**
	 * Render the header of this Form as Html.
	 */
	public function header() {
		$options = array('action' => $this->action, 'method' => $this->method);
		$options = array_merge($options, $this->options);
		return Html::openTag('form', $options);
	}

	/**
	 * Render the label of FormRow $name as Html.
	 *
	 * @param string $name The name of the FormRow label to render.
	 */
	public function label($name) {
		return $this->getRow($name)->label();
	}

	/**
	 * Render the input of FormRow $name as Html.
	 *
	 * @param string $name The name of the FormRow input to render.
	 */
	public function input($name) {
		return $this->getRow($name)->input();
	}

	/**
	 * Render the error of FormRow $name as Html.
	 *
	 * @param string $name The name of the FormRow error to render.
	 */
	public function error($name) {
		return $this->getRow($name)->error();
	}

	/**
	 * Render the FormRow $name as Html.
	 *
	 * @param string $name The name of the FormRow render.
	 */
	public function row($name) {
		return $this->getRow($name)->render();
	}

	/**
	 * Render the entire Form as Html.
	 */
	public function render() {
		$form = $this->header();
		foreach($this->rows as $row) {
			$form .= $row->render();
		}
		$form .= '</form>';
		return $form;
	}

	public function __toString() {
		return $this->render();
	}

	protected function addRow($type, $name, $value = null, $options = array()) {
		$this->rows[$name] = new FormRow($type, $name, $value, $options);
		return $this;
	}

	public function getRow($name) {
		if(!array_key_exists($name, $this->rows)) {
			throw new \Exception(
				"Unknown form row '$name'"
			);
		}
		return $this->rows[$name];
	}

	public function set($name, $value, $create_row = false) {
		if(!array_key_exists($name, $this->rows)) {
			if(!$create_row) {
				throw new \Exception(
					"Attempting to assign value '$value' to an unknown form row '$name'"
				);
			}
			return $this->text($name, $value);
		}
		$this->rows[$name]->setValue($value);
		return $this;
	}

	public function get($name) {
		return $this->getRow($name)->getValue();
	}

	public function setValues(array $values=array(), $create_row = false) {
		foreach ($values as $name => $value) {
			$this->set($name, $value, $create_row);
		}
		return $this;
	}

	public function getValues() {
		$values = array();
		foreach ($this->rows as $name => $row) {
			$values[$name] = $row->getValue();
		}
		return $values;
	}

	public function addErrors(array $errors = array()) {
		foreach ($errors as $name => $msg) {
			$this->getRow($name)->setError($msg);
		}
	}

	public function text($name, $value = null, $options = array()) {
		return $this->addRow('text', $name, $value, $options);
	}

	public function password($name, $value = null, $options = array()) {
		return $this->addRow('password', $name, $value, $options);
	}

	public function textarea($name, $value = null, $options = array()) {
		return $this->addRow('textarea', $name, $value, $options);
	}

}

<?php

namespace Neptune\Form;

use Neptune\Helpers\Html;

/**
 * Form
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Form {

	protected $action;
	protected $method;
	protected $options = array();
	protected $rows = array();

	public function __construct($action, $method = 'post', $options = array()) {
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

	/**
	 * Get the FormRow instance with name $name.
	 *
	 * @param string $name The name of the FormRow instance to get.
	 */
	public function getRow($name) {
		if(!array_key_exists($name, $this->rows)) {
			throw new \Exception(
				"Attempting to access unknown form row '$name'"
			);
		}
		return $this->rows[$name];
	}

	/**
	 * Get a list of field names in this form.
	 *
	 * @return array An array of field names.
	 */
	public function getFields() {
		return array_keys($this->rows);
	}

	/**
	 * Set the value of the input attached to FormRow $name. If
	 * $create_row is true, a new FormRow of name $name will be
	 * created with type 'text'. Otherwise, an Exception will be
	 * thrown if the FormRow doesn't exist.
	 *
	 * @param string $name The name of the FormRow
	 * @param string $value The value
	 * @param bool $create_row Create a new FormRow if it doesn't exist
	 */
	public function setValue($name, $value, $create_row = false) {
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

	/**
	 * Get the value of the input attached to FormRow $name.
	 */
	public function getValue($name) {
		return $this->getRow($name)->getValue();
	}

	/**
	 * Set the value of the input in multiple FormRows. If
	 * $create_rows is true, new FormRows will be created with type
	 * 'text' if they don't exist. Otherwise, an Exception will be
	 * thrown if a FormRow doesn't exist.
	 *
	 * @param array $values An array of keys and values to set
	 * @param bool $create_row Create a new FormRow if it doesn't exist
	 */
	public function setValues(array $values = array(), $create_rows = false) {
		foreach ($values as $name => $value) {
			$this->setValue($name, $value, $create_rows);
		}
		return $this;
	}

	/**
	 * Get the values of all inputs attached to this form.
	 */
	public function getValues() {
		$values = array();
		foreach ($this->rows as $name => $row) {
			$values[$name] = $row->getValue();
		}
		return $values;
	}

    /**
     * Set the error of FormRow $name.
     *
     * @param string $name The name of the FormRow
     * @param string $error The error message
     */
    public function setError($name, $error)
    {
        return $this->getRow($name)->setError($error);
    }

    /**
     * Get the error of FormRow $name.
     *
     * @param string $name The name of the FormRow
     */
    public function getError($name)
    {
        return $this->getRow($name)->getError();
    }

	/**
	 * Add multiple errors to this Form. $errors should be an array of
	 * keys and values, where a key is a name of a FormRow attached to
	 * this form, and a value is the error message.
	 *
	 * @param array $errors An array of names and errors
	 */
	public function addErrors(array $errors = array()) {
		foreach ($errors as $name => $msg) {
			$this->setError($name, $msg);
		}
	}

    /**
     * Get all of the errors attached to this Form.
     *
     * @return array An array of errors
     */
    public function getErrors()
    {
        return array_map(function($row) {
                return $row->getError();
            }, $this->rows);
    }

	/**
	 * Add a text input to the form.
	 *
	 * @param string $name The name of the input
	 * @param string $value The initial value of the input
	 * @param array $options Additional Html options to add to the input
	 */
	public function text($name, $value = null, $options = array()) {
		return $this->addRow('text', $name, $value, $options);
	}

	/**
	 * Add a password field to the form. $value will not be added to
	 * the password input for security reasons, though it is available
	 * through getValue('$name'). If you understand the security
	 * implications and still want to create a password field with a
	 * default value, you could construct the HTML manually using the
	 * Html class, e.g.
	 *
	 * <?=$f->label('pass');?>
	 * <?=Html::input('password', 'pass', $f->getValue('pass'), array('id' => 'password')'?>
	 * <?=$f->error('pass');?>
	 *
	 * @param string $name The name of the input
	 * @param string $value The initial value of the input (not shown)
	 * @param array $options Additional Html options to add to the input
	 */
	public function password($name, $value = null, $options = array()) {
		return $this->addRow('password', $name, $value, $options);
	}

	/**
	 * Add a textarea to the form.
	 *
	 * @param string $name The name of the input
	 * @param string $value The initial value of the input
	 * @param array $options Additional Html options to add to the input
	 */
	public function textarea($name, $value = null, $options = array()) {
		return $this->addRow('textarea', $name, $value, $options);
	}

	/**
	 * Add a submit field to the form. No label or error
	 * message is rendered for this type. If required, the label and
	 * error message are available from label() and error(). If $value
	 * is not supplied, the submit button will be given a value
	 * automatically.
	 *
	 * @param string $name The name of the input
	 * @param string $value The initial value of the input
	 * @param array $options Additional Html options to add to the input
	 */
	public function submit($name, $value = null, $options = array()) {
		return $this->addRow('submit', $name, $value, $options);
	}

	/**
	 * Add a hidden field to the form. Aside from the input tag, no
	 * HTML is rendered for this type. If required, the label and
	 * error message are available from label() and error().
	 *
	 * @param string $name The name of the input
	 * @param string $value The initial value of the input
	 * @param array $options Additional Html options to add to the input
	 */
	public function hidden($name, $value = null, $options = array()) {
		return $this->addRow('hidden', $name, $value, $options);
	}

	/**
	 * Add a checkbox field to the form. By convention, the value of
	 * the input tag will always be 'checked'. If required, the real
	 * value is available from getValue(). If the $value is truthy, a
	 * checked attribute will be added automatically.
	 *
	 * @param string $name The name of the input
	 * @param string $value The initial value of the input
	 * @param array $options Additional Html options to add to the input
	 */
	public function checkbox($name, $value = null, $options = array()) {
		return $this->addRow('checkbox', $name, $value, $options);
	}

}

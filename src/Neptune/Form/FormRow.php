<?php

namespace Neptune\Form;

use Neptune\Helpers\Html;

use Stringy\Stringy as S;

/**
 * FormRow
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FormRow {

	protected $type;
	protected $name;
	protected $value;
	//only applicable for selects and radios
	protected $choices = array();
	protected $options;
	protected $label;
	protected $error;
	protected $row_string = ':label:input:error';

	public function __construct($type, $name, $value = null, $options = array()) {
		$this->type = $type;
		$this->name = $name;
		$label = $this->sensible($name);
		$this->label = $label;
		if($type === 'submit' && $value === null) {
			$value = $label;
		}
		$this->setValue($value);
		$this->options = $options;
	}

	/**
	 * Create a sensible, human readable default for $string,
	 * e.g. creating a label for the name of form inputs.
	 *
	 * @param string $string the string to transform
	 */
	protected function sensible($string) {
		return ucfirst(
			(string) S::create($string)
			->underscored()
			->replace('_', ' ')
			->trim()
		);
	}

	/**
	 * Set the error message attached to this FormRow.
	 *
	 * @param string $error The error message.
	 */
	public function setError($error) {
		$this->error = $error;
		return $this;
	}

	/**
	 * Get the error message attached to this FormRow.
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * Render the error message attached to this FormRow as Html.
	 */
	public function error() {
		if($this->error) {
			return '<p>' . $this->error . '</p>';
		}
		return null;
	}

	/**
	 * Set the label text attached to this FormRow.
	 *
	 * @param string $label The label.
	 */
	public function setLabel($label) {
		$this->label = $label;
		return $this;
	}

	/**
	 * Get the label text attached to this FormRow.
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * Render the label attached to this FormRow as Html.
	 */
	public function label() {
		return Html::label($this->name, $this->label);
	}

	/**
	 * Set the value of the input attached to this FormRow.
	 *
	 * @param string $value The value.
	 */
	public function setValue($value) {
		$this->value = $value;
		return $this;
	}

	/**
	 * Get the value of the input attached to this FormRow.
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Render the input attached to this FormRow as Html.
	 */
	public function input() {
		if($this->type === 'select') {
			$selected = $this->value;
			return Html::select($this->name, $this->choices, $selected, $this->options);
		}

		switch ($this->type) {
		//if input is a checkbox and it has a truthy value, add
		//checked to options before render
		case 'checkbox':
			if($this->value !== null) {
				$this->addOptions(array('checked'));
			}
			//no matter what, the value of the input is 'checked'
			$value = 'checked';
			break;
		case 'password';
			//remove the value from all password fields
			$value = null;
			break;
		default:
			$value = $this->value;
		}

		return Html::input($this->type, $this->name, $value, $this->options);
	}

	/**
	 * Set the type of input attached to this FormRow.
	 *
	 * @param string $type The input type.
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * Get the type of input attached to this FormRow.
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Set the html options of the input attached to this FormRow. All
	 * previous options will be reset.
	 *
	 * @param array $options An array of keys and values
	 */
	public function setOptions(array $options) {
		$this->options = $options;
		return $this;
	}

	/**
	 * Add to the html options of the input attached to this FormRow.
	 *
	 * @param array $options An array of keys and values
	 */
	public function addOptions(array $options) {
		$this->options = array_merge($this->options, $options);
		return $this;
	}

	/**
	 * Get the html options of the input attached to this FormRow.
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * Return true if this FormRow can use choices. Throw an Exception
	 * otherwise.
	 */
	protected function checkCanUseChoices() {
		if($this->type === 'select' || $this->type === 'radio') {
			return true;
		}
		throw new \Exception("Choices are only allowed with a FormRow of type 'select' or 'radio', '$this->name' has disallowed type '$this->type'");
	}


	/**
	 * Set the choices for the input attached to this FormRow. If no
	 * keys are given in the choices array or, due to PHP's array
	 * implementation, keys are strings containing valid integers,
	 * keys will be created automatically by calling
	 * FormRow::sensible. An Exception will be thrown if this FormRow
	 * does not have the type 'radio' or 'select'.
	 *
	 * @param array $choices An array of keys and values to use in
	 * option tags
	 */
	public function setChoices(array $choices) {
		$this->checkCanUseChoices();
		$this->choices = array();
		$this->addChoices($choices);
		return $this;
	}

	/**
	 * Add to the choices for the input attached to this FormRow. If
	 * no keys are given in the choices array or, due to PHP's array
	 * implementation, keys are strings containing valid integers,
	 * keys will be created automatically by calling
	 * FormRow::sensible. An Exception will be thrown if this FormRow
	 * does not have the type 'radio' or 'select'.
	 *
	 * @param array $choices An array of keys and values to use in
	 * option tags
	 */
	public function addChoices(array $choices) {
		foreach ($choices as $k => $v) {
			if(is_int($k)) {
				$k = $this->sensible($v);
			}
			$this->choices[$k] = $v;
		}
		return $this;
	}

	/**
	 * Get the choices for the input attached to this FormRow. An
	 * Exception will be thrown if this FormRow does not have the type
	 * 'radio' or 'select'.
	 */
	public function getChoices() {
		$this->checkCanUseChoices();
		return $this->choices;
	}

	/**
	 * Render this FormRow instance as Html, with label, input and
	 * error message, if available.
	 */
	public function render() {
		//a hidden field should be just an input
		if($this->type == 'hidden' ) {
			return $this->input();
		}
		//a submit field should be just an input, but with extra html
		//set in $this->row_string
		if ($this->type == 'submit') {
			$str = str_replace(':error', '', $this->row_string);
			$str = str_replace(':label', '', $str);
			$str = str_replace(':input', $this->input(), $str);
			return $str;
		}
		//otherwise, substitute :label, :input and :error into
		//$this->row_string
		$str = str_replace(':label', $this->label(), $this->row_string);
		$str = str_replace(':error', $this->error(), $str);
		$str = str_replace(':input', $this->input(), $str);
		return $str;
	}

}

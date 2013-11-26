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
	protected $options;
	protected $label;
	protected $error;
	protected $row_string = ':label:input:error';

	public function __construct($type, $name, $value = null, $options = array()) {
		$this->type = $type;
		$this->name = $name;
		//create a sensible, human readable default for the label
		$label = ucfirst(S::create($name)->underscored()->replace('_', ' ')->str);
		$this->label = $label;
		if($type === 'submit' && $value === null) {
			$value = $label;
		}
		$this->value = $value;
		$this->options = $options;
	}

	/**
	 * Set the error message attached to this FormRow.
	 *
	 * @param string $error The error message.
	 */
	public function setError($error) {
		$this->error = $error;
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
	}

	/**
	 * Get the value of the input attached to this FormRow.
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Render the label attached to this FormRow as Html.
	 */
	public function input() {
		return Html::input($this->type, $this->name, $this->value, $this->options);
	}

	/**
	 * Get the type of input attached to this FormRow.
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Set the type of input attached to this FormRow.
	 *
	 * @param string $type The input type.
	 */
	public function setType($type) {
		$this->type = $type;
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

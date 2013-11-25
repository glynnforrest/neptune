<?php

namespace Neptune\Form;

use Neptune\Helpers\Html;

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
		$this->label = ucfirst($name);
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
		return Html::tag(
			'label', $this->label, array(
				'for' => $this->name, 'id' => $this->name
			)
		);
	}

	/**
	 * Render the label attached to this FormRow as Html.
	 */
	public function input() {
		return Html::input($this->type, $this->name, $this->value, $this->options);
	}

	/**
	 * Render this FormRow instance as Html, with label, input and
	 * error message, if available.
	 */
	public function render() {
		//a hidden field should be just an input
		if($this->type == 'hidden' ) {
			return $this->input($name);
		}
		//a submit field should be just an input, but with extra html
		//set in $this->row_string
		if ($this->type == 'submit') {
			$str = str_replace(':error', '', $this->row_string);
			$str = str_replace(':label', '', $str);
			$str = str_replace(':input', $this->input($name), $str);
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

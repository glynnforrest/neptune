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
	protected $error;

	public function __construct($type, $name, $value = null, $options = array()) {
		$this->type = $type;
		$this->name = $name;
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
		return '<p>' . $this->error . '</p>';
	}

	/**
	 * Render this FormRow instance as Html, with label, input and
	 * error message, if available.
	 */
	public function render() {
		$row = Html::tag(
			'label', ucfirst($this->name), array(
				'for' => $this->name, 'id' => $this->name
			)
		);
		$row .= Html::input($this->type, $this->name, $this->value, $this->options);
		return $row;
	}


}

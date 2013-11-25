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

	public function __construct($type, $name, $value = null, $options = array()) {
		$this->type = $type;
		$this->name = $name;
		$this->value = $value;
		$this->options = $options;
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

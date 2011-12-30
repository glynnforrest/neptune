<?php

/**
 * FormValidator
 * @author Glynn Forrest <me@glynnforrest.com>
 */

namespace neptune\validate;

class FormValidator extends Validator {

	public function __construct() {
		parent::__construct('POST');
	}

	//TODO: implement this to check for form forgery!
	public function checkFormToken() {
		return true;
	}

	public function getFormErrors() {
		$errors = $this->errors;
		foreach ($errors as $k => $v) {
			if (in_array($k, $this->missing)) {
				$errors[$k] = ucfirst($k) . ' field is empty.';
			}
		}
		foreach ($this->missing as $missing) {
			if (!isset($errors[$missing])) {
				$errors[$missing] = ucfirst($missing) . ' field is empty.';
			}
		}
		return $errors;
	}

}

?>

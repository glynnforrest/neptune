<?php

namespace neptune\view;

use neptune\helpers\Html;

/**
 * Form
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Form extends View {

	protected $header;
	protected $errors = array();
	protected $types = array();
	protected $options = array();
	protected $row_string = '<li>:label:input:error</li>';
	//todo add strings for each.

	public static function load($view, $values = array(), $errors = array()) {
		$me = parent::loadAbsolute($view, $values);
		$me->addErrors($errors);
		return $me;
	}

	public function render() {
		$form = Html::formHeader($this->header[0],
			$this->header[1], $this->header[2]);
		$form .= '<ul>';
		foreach($this->vars as $k => $v) {
			$form .= $this->row($k);
		}
		$form .= '</ul></form>';
		return $form;
	}

	public function addErrors($errors) {
		$this->errors = $errors;
	}

	public function header($action, $method = 'post', $options = array()) {
		$this->header = array($action, $method, $options);
	}

	public function add($type, $name, $value = null, $options = array()) {
		$this->types[$name] = $type; 
		$this->vars[$name] = $value;
		if($options) {
			$this->options[$name] = $options; 
		}
	}

	public function input($name) {
		$value = isset($this->vars[$name]) ? $this->vars[$name] : null;
		$type = isset($this->types[$name]) ? $this->types[$name] : 'text';
		$options = isset($this->options[$name]) ? $this->options[$name] :
			array();
		return Html::input($type, $name, $value, $options);
	}

	public function error($name) {
		return isset($this->errors[$name]) ? $this->errors[$name] : null;
	}

	public function label($name) {
		return '<label for="' . $name .'">' . $name . '</label>';
	}

	public function row($name) {
		$type = isset($this->types[$name]) ? $this->types[$name] : null;
		if($type) {
			if($type == 'hidden' ) {
				return $this->input($name);
			} elseif ($type == 'submit') {
				$str = str_replace(':error', '', $this->row_string);
				$str = str_replace(':label', '', $str);
				$str = str_replace(':input', $this->input($name), $str);
				return $str;
			} else {
				$str = str_replace(':label', $this->label($name), $this->row_string);
			}
		} else {
			$str = str_replace(':label', $this->label($name), $this->row_string);
		}
		$str = str_replace(':error', $this->error($name), $str);
		$str = str_replace(':input', $this->input($name), $str);
		return $str;
	}

	public function setType($name, $type) {
		$this->types[$name] = $type;
	}

	public function setOptions($name, $options) {
		$this->options[$name] = $options;
	}

}
?>
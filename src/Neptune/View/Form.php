<?php

namespace Neptune\View;

use Neptune\Helpers\Html;
use Neptune\Http\Request;

/**
 * Form
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Form extends View {

	protected $header;
	protected $fields = array();
	protected $errors = array();
	protected $types = array();
	protected $options = array();
	protected $row_string = '<li>:label:input:error</li>';
	protected $selected = array();
	//todo add strings for each.

	public static function loadAbsolute($view, $values = array(), $errors = array()) {
		$me = parent::loadAbsolute($view);
		$me->setValues($values, true);
		$me->addErrors($errors);
		return $me;
	}

	public static function load($view, $values = array(), $errors = array(), $action = null) {
		$me = parent::load($view);
		$me->setValues($values, true);
		$me->addErrors($errors);
		if($action) {
			$me->header[0] = $action;
		}
		return $me;
	}

	public static function create($action = null, $method = 'post', $options = array()) {
		if(!$action) {
			$action = Request::getInstance()->uri();
		}
		$form = new self();
		$form->setHeader($action, $method, $options);
		return $form;
	}

	public function render() {
		if(isset($this->view)) {
			return parent::render();
		}
		return $this->renderForm();
	}

	public function setValues(array $values=array(), $create_fields = false) {
		foreach ($values as $k => $v) {
			if ($create_fields || in_array($k, $this->fields)) {
				$this->vars[$k] = $v;
				if(!in_array($k, $this->fields)) {
					$this->fields[] = $k;
				}
			}
		}
		return $this;
	}

	public function renderForm() {
		$form = $this->header();
		$form .= '<ul>';
		foreach($this->vars as $k => $v) {
			$form .= $this->row($k);
		}
		$form .= '</ul></form>';
		return $form;
	}

	public function addErrors($errors) {
		$this->errors = $errors;
		return $this;
	}

	public function setHeader($action, $method = 'post', $options = array()) {
		$this->header = array($action, $method, $options);
		return $this;
	}

	public function add($name, $type = 'text', $value = null, $options = array()) {
		$this->types[$name] = $type;
		$this->vars[$name] = $value;
		$this->fields[] = $name;
		if($options) {
			$this->options[$name] = $options;
		}
		return $this;
	}

	public function createFields($list = array()) {
		foreach ($list as $item) {
			$this->vars[$item] = null;
		}
		return $this;
	}

	public function header() {
		$options = $this->header[2];
		$options['action'] = $this->header[0];
		$options['method'] = $this->header[1];
		return Html::openTag('form', $options);
	}

	public function input($name) {
		if(!in_array($name, $this->fields)) {
			return null;
		}
		$value = isset($this->vars[$name]) ? $this->vars[$name] : null;
		$type = isset($this->types[$name]) ? $this->types[$name] : 'text';
		$options = isset($this->options[$name]) ? $this->options[$name] :
			array();
		if(is_array($value)) {
			$selected = isset($this->selected[$name]) ? $this->selected[$name] : null;
			return Html::select($name, $value, $selected, $options);
		}
		return Html::input($type, $name, $value, $options);
	}

	public function error($name) {
		return isset($this->errors[$name]) ? $this->errors[$name] : null;
	}

	public function label($name) {
		return '<label for="' . $name .'">' . ucfirst($name) . '</label>';
	}

	public function row($name) {
		if(!in_array($name, $this->fields)) {
			return null;
		}
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
		return $this;
	}

	public function setOptions($name, $options) {
		$this->options[$name] = $options;
		return $this;
	}

	public function setSelected($name, $selected) {
		$this->selected[$name] = $selected;
		return $this;
	}

}
?>

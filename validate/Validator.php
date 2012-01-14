<?php

/**
 * Validator
 * @author Glynn Forrest <me@glynnforrest.com>
 */

namespace neptune\validate;

use neptune\database\DBObject;
use neptune\helpers\String;
use neptune\http\Session;
use neptune\system\ArrayDataSource;

class Validator implements ArrayDataSource {

	protected $input_array;
	protected $fails = array();
	protected $errors = array();
	protected $checked = false;
	protected $can_fail = array();
	protected $messages = array(
		 'untested' => 'No validation rules specified.',
		 'nomethod' => ':name was checked with an unknown method.',
		 'undefined' => ':name is not in input.',
		 'alpha' => ':name is not alphabetical.',
		 'alphanum' => ':name is not alphanumeric.',
		 'alphadash' => ':name contains disallowed characters.',
		 'between' => ':name is not between :min and :max characters in length.',
		 'email' => ':value is not a valid email address.',
		 'hex' => ':name is not hexadecimal.',
		 'int' => ':name is not an integer.',
		 'length' => ':name is not :length characters long.',
		 'matches' => ':name does not match :match.',
		 'max' => ':name must be less than :max characters.',
		 'min' => ':name must have at least :min characters.',
		 'num' => ':name is not a number.',
		 'required' => ':name is required.',
		 'token' => 'Your session has expired.',
		 'url' => ':name is not a url.'
	);
	protected $parsed = array();

	public function __construct($input_array = 'POST', array $rules = array(), array $messages = array()) {
		if (is_array($input_array)) {
			$this->input_array = $input_array;
		} else {
			switch (strtoupper($input_array)) {
				case 'POST':
					$this->input_array = $_POST;
					break;
				case 'GET':
					$this->input_array = $_GET;
					break;
				default:
					return false;
			}
		}
		foreach ($messages as $k => $v) {
			$this->setMessage($k, $v);
		}
		foreach ($rules as $k => $v) {
			$this->check($k, $v);
		}
		return true;
	}

	public function __get($value) {
		return isset($this->input_array[$value]) ? $this->input_array[$value] : null;
	}

	public function __isset($name) {
		return isset($this->input_array[$name]);
	}

	public function addValues(array $values) {
		foreach ($values as $k => $v) {
			$this->input_array[$k] = $v;
		}
		return true;
	}

	public function getValues() {
		return $this->input_array;
	}

	public function check($name, $validator_string) {
		$this->checked = true;
		if (!array_key_exists($name, $this->input_array)) {
			$this->fail($name, 'undefined');
			$this->can_fail[] = $name;
			return false;
		}
		$rules = explode('|', $validator_string);
		if (empty($rules)) {
			return false;
		}
		if(in_array('required', $rules)) {
			$this->can_fail[] = $name;
			if(!$this->checkRequired($this->input_array[$name])) {
				$this->fail($name, 'required');
				return false;
			}
			//TODO: remove required from rules so it isn't run again.
		} else {
			if($this->checkRequired($this->input_array[$name] )) {
				$this->can_fail[] = $name;
			}
		}
		foreach ($rules as $rule) {
			$len = strpos($rule, ':') ? : strlen($rule);
			$type = substr($rule, 0, $len);
			$method = 'check' . ucfirst($type);
			if (!method_exists($this, $method)) {
				$this->fail($name, 'nomethod');
				$this->can_fail[] = $name;
				return false;
			}
			$args = array($this->input_array[$name]);
			if ($len != strlen($rule)) {
				$args = array_merge($args, explode(',', substr($rule, $len + 1)));
			}
			if (@call_user_func_array(array($this, $method), $args)) {
				continue;
			} else {
				$this->fail($name, $type);
				return false;
			}
		}
		return true;
	}

	protected function fail($name, $type) {
		if (in_array($name, $this->fails)) {
			return true;
		}
		if (!array_key_exists($name, $this->errors)) {
			$this->fails[] = $name;
			$msg = $this->getMessage($name, $type);
			if ($msg) {
				$msg = str_replace(':name', String::spaces($name), $msg);
				$msg = isset($this->input_array[$name]) && !empty($this->input_array[$name]) ?
						  str_replace(':value', $this->input_array[$name], $msg) : $msg;
			}
			$this->errors[$name] = ucfirst($msg);
		}
	}

	protected function getMessage($name, $type) {
		if (isset($this->parsed[$type . '_' . $name])) {
			return $this->parsed[$type . '_' . $name];
		}
		if (isset($this->parsed[$type])) {
			return $this->parsed[$type];
		}
		if (isset($this->messages[$type . '_' . $name])) {
			return $this->messages[$type . '_' . $name];
		}
		return isset($this->messages[$type]) ? $this->messages[$type] : null;
	}

	public function setMessage($type, $message) {
		if (isset($this->messages[$type])) {
			$this->messages[$type] = $message;
			return true;
		}
		$len = strpos($type, ':') ? : strlen($type);
		$rule = substr($type, 0, $len);
		$types = explode(',', substr($type, $len + 1));
		foreach ($types as $t) {
			$this->messages[$rule . '_' . $t] = $message;
		}
		return true;
	}

	protected function parse($type, array $variables = array()) {
		$keys = preg_grep('/^' . $type . '/', array_keys($this->messages));
		foreach ($keys as $key) {
			$msg = $this->messages[$key];
			foreach ($variables as $k => $v) {
				$msg = str_replace($k, $v, $msg);
			}
			$this->parsed[$key] = $msg;
		}
		return true;
	}

	public function errors($name = null) {
		if($name) {
			return isset($this->errors[$name]) ? $this->errors[$name] : null;
		}
		$errors = array();
		foreach($this->errors as $k => $v) {
			if(in_array($k, $this->can_fail)) {
				$errors[$k] = $v;
			}
		}
		return $errors;
	}

	protected function checkRequired($value) {
		$v = is_array($value) ? $value : trim($value);
		if (empty($v) && !is_numeric($v)) {
			return false;
		}
		return true;
	}

	protected function checkInt($value) {
		return filter_var($value, FILTER_VALIDATE_INT) !== false;
	}

	protected function checkNum($value) {
		return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
	}

	protected function checkHex($value) {
		return ctype_xdigit($value);
	}

	protected function checkAlpha($value) {
		return ctype_alpha($value);
	}

	protected function checkAlphanum($value) {
		return ctype_alnum($value);
	}

	protected function checkAlphadash($value) {
		$value = str_replace(array('_', '-'), '', $value);
		return ctype_alnum($value);
	}

	protected function checkLength($value, $length) {
		if (strlen($value) == $length) {
			return true;
		} else {
			$this->parse('length', array(':length' => $length));
			return false;
		}
	}

	protected function checkMin($value, $min) {
		if (strlen($value) >= $min) {
			return true;
		} else {
			$this->parse('min', array(':min' => $min));
			return false;
		}
	}

	protected function checkMax($value, $max) {
		if (strlen($value) <= $max) {
			return true;
		} else {
			$this->parse('max', array(':max' => $max));
			return false;
		}
	}

	protected function checkBetween($value, $min, $max) {
		if (strlen($value) >= $min && strlen($value) <= $max) {
			return true;
		} else {
			$this->parse('between', array(':min' => $min, ':max' => $max));
			return false;
		}
	}

	protected function checkEmail($value) {
		return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
	}

	protected function checkMatches($value, $match) {
		if (!array_key_exists($match, $this->input_array)) {
			$this->parse('matches', array(':match' => 'undefined'));
			return false;
		}
		if ($this->input_array[$match] === $value) {
			return true;
		} else {
			$this->parse('matches', array(':match' => $match));
			return false;
		}
	}

	protected function checkUrl($value) {
		return filter_var($value, FILTER_VALIDATE_URL) !== false;
	}

	protected function checkToken($value) {
		return $value == Session::token();
	}

	public function validate() {
		if ($this->checked) {
			return $this->checkTests();
		} else {
			$this->fail('all', 'untested');
			return false;
		}
	}

	protected function checkTests() {
		if (empty($this->fails)) {
			return true;
		} else {
			foreach ($this->fails as $fail) {
				if (in_array($fail, $this->can_fail)) {
					return false;
				}
			}
		}
		return true;
	}

}

?>

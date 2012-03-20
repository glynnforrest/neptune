<?php

namespace neptune\database;

/**
 * Relationship
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Relationship {

	const TYPE_ONE_TO_ONE = 0;
	const TYPE_ONE_TO_MANY = 1;
	const TYPE_MANY_TO_MANY = 2;


	protected $type;
	protected $left;
	protected $right;
	protected $left_key;
	protected $right_key;

	public function __construct($type, $left_key, $right_key) {
		$this->type = $type;
		$this->left_key = $left_key;
		$this->right_key = $right_key;
	}

	public function setObject($key, &$object) {
		if($key === $this->left_key) {
			$this->left = $object;
		} elseif($key === $this->right_key) {
			$this->right = $object;
		}
	}

	public function setRelatedObject($key, &$related_object) {
		if($key === $this->left_key) {
			$this->right = $related_object;
		} elseif($key === $this->right_key) {
			$this->left = $related_object;
		}
	}

	public function getRelatedObject($key) {
		if($key === $this->left_key) {
			return $this->right;
		} elseif($key === $this->right_key) {
			return $this->left;
		}
	}

	public function getLeftKey() {
		return $this->left_key;
	}

	public function getRightKey() {
		return $this->right_key;
	}

	public function setKey($key, $value) {
		if($key === $this->left_key) {
			$name = $this->right_key;
			$this->right->$name = $value;
		} 
	}

}
?>

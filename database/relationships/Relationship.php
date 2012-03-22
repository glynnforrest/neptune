<?php

namespace neptune\database\relationships;

/**
 * Relationship
 * @author Glynn Forrest me@glynnforrest.com
 **/
abstract class Relationship {

	protected $left;
	protected $right;
	protected $left_key;
	protected $right_key;
	protected $left_class;
	protected $right_class;

	public function __construct($left_key, $left_class, $right_key, $right_class) {
		$this->left_key = $left_key;
		$this->right_key = $right_key;
		$this->left_class = $left_class;
		$this->right_class = $right_class;
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
			return $this->right();
		} elseif($key === $this->right_key) {
			return $this->left();
		}
	}

	abstract protected function left();

	abstract protected function right();

	public function updateKey($key, $value) {
		if($key === $this->left_key) {
			$name = $this->right_key;
			if($this->right) {
				$this->right->$name = $value;
			}
		} 
	}

}
?>

<?php

namespace neptune\database\relationships;

/**
 * OneToOne
 * @author Glynn Forrest me@glynnforrest.com
 **/
class OneToOne extends Relationship {

	protected function left() {
		if(!isset($this->left)) {
			$model = $this->left_class;
			$this->left = $model::selectOne($this->left_key,
				$this->right->$this->right_key);
		}
		return $this->left;
	}

	protected function right() {
		if(!isset($this->right)) {
			$model = $this->right_class;
			$class = $this->left;
			$left_key = $this->left_key;
			$this->right = $model::selectOne($this->right_key,
				$this->left->$left_key);
		}
		return $this->right;

	}
}
?>

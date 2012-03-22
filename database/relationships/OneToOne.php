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
			$right_key = $this->right_key;
			$this->left = $model::selectOne($this->left_key,
				$this->right->$right_key);
		}
		$this->left->addRelationship($model . $this->left_key,
			$this->left_key, $this);
		return $this->left;
	}

	protected function right() {
		if(!isset($this->right)) {
			$model = $this->right_class;
			$left_key = $this->left_key;
			$this->right = $model::selectOne($this->right_key,
				$this->left->$left_key);
		}
		$this->right->addRelationship($model . $this->right_key,
			$this->right_key, $this);
		return $this->right;

	}
}
?>

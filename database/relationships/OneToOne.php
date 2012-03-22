<?php

namespace neptune\database\relationships;

/**
 * OneToOne
 * @author Glynn Forrest me@glynnforrest.com
 **/
class OneToOne extends Relationship {

	
	public function getRelatedObject($key) {
		if($key === $this->left_key) {
			return $this->right;
		} elseif($key === $this->right_key) {
			return $this->left;
		}
	}

}
?>

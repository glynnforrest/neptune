<?php

namespace neptune\database;

use neptune\database\DatabaseModel;

/**
 * RelationsManager
 * @author Glynn Forrest me@glynnforrest.com
 **/
class RelationsManager {

	protected static $instance;

	protected function __construct() {
	}

	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function updateRelation(&$object, &$related_object, $relation) {
		$key = $relation['key'];
		$foreign_key = $relation['foreign_key'];
		if(isset($object->$key)) {
			$related_object->$foreign_key = $object->$key;
		}
	}

	public function processRelation(&$object, array $relation) {
		// $type = $relation['type'];
		// if($type === 'has_one') {
			return $this->processHasOne($object, $relation);
		//}
	}

	protected function processHasOne(&$object, array $relation) {
		$model = $relation['model'];
		$relation = $model::selectOne($relation['foreign_key'],
			$object->$relation['key']);
		if($relation) {
			$object->setRelation('message', $relation);
		}
	}
}
?>

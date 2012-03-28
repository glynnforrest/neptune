<?php

namespace neptune\database\relations;

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

	public function createRelation($obj, $name, array $relation, $related_object = null) {
		$type = $relation['type'];
		$key = $relation['key'];
		$other_key = $relation['other_key'];
		$other_class = $related_object ? $related_object : $relation['other_class'];
		switch ($type) {
			case 'has_one':
				return $this->hasOne($obj, $name, $key, $other_key, $other_class);
				break;
			case 'belongs_to':
				return $this->belongsTo($obj, $name, $key, $other_key, $other_class);
				break;
			default:
				break;
		}
	}

	protected function hasOne($obj, $name, $key, $other_key, $other_class) {
		if(is_object($other_class)) {
			//setting related object
			$r = new OneToOne($key, get_class($obj), $other_key,
				get_class($other_class));
			$r->setObject($other_key, $other_class);
			$obj->addRelation($name, $key, $r);
		} else {
			//getting related object
			$obj->addRelation($name, $key, new OneToOne(
				$key, get_class($obj), $other_key, $other_class));
		}
	}

	protected function belongsTo($obj, $name, $key, $other_key, $other_class) {
		if(is_object($other_class)) {
			//setting related object
			$r = new OneToOne($other_key, get_class($other_class), $key,
				get_class($obj));
			$r->setObject($other_key, $other_class);
			$obj->addRelation($name, $key, $r);
		} else {
			//getting related object
			$obj->addRelation($name, $key, new OneToOne($other_key,
				$other_class, $key, get_class($obj)));
		}
	}
}
?>

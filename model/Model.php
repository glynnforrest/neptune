<?php

namespace neptune\model;

use neptune\cache\Cacheable;

/**
 * Model
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Model extends Cacheable {

	protected static $models = array();

	protected function __construct() { 
	}

	public static function getInstance($database = false) {
		$class = get_called_class();
		$model_name = $database ? $class . '.' . $database : $class;
		if (!isset(self::$models[$model_name])) {
			self::$models[$model_name] = new $class();
			self::$models[$model_name]->setDatabase($database);
		}
		return self::$models[$model_name];
	}

}
?>

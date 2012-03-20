<?php

namespace neptune\model;

use neptune\database\SQLQuery;
use neptune\database\DatabaseFactory;
use neptune\database\DBObject;
use neptune\database\DBObjectSet;
use neptune\database\Relationship;
use neptune\validate\Validator;
use neptune\cache\Cacheable;
use neptune\exceptions\TypeException;
use neptune\view\Form;

/**
 * DatabaseModel
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class DatabaseModel extends Cacheable {

	protected static $models = array();
	protected $database;
	protected $table;
	protected $fields = array();
	protected $primary_key = 'id';
	protected $rules = array();
	protected $messages = array();
	protected $relationships = array();

	/**
	 *
	 * @param string $database
	 * @return DatabaseModel 
	 */
	public static function getInstance($database = false) {
		$class = get_called_class();
		$model_name = $database ? $class . '.' . $database : $class;
		if (!isset(self::$models[$model_name])) {
			self::$models[$model_name] = new $class();
			self::$models[$model_name]->setDatabase($database);
		}
		return self::$models[$model_name];
	}

	protected function __construct() {
		
	}

	/**
	 * Create a DBObject, apply mapping from this model and return to the user.
	 * @param string $database
	 * @return DBObject
	 */
	public static function createOne($data = array(), $database = false) {
		$me = self::getInstance($database);
		$obj = new DBObject($database, $me->table);
		$me->applySchema($obj);
		foreach($data as $k => $v) {
			$obj->$k = $v;
		}
		return $obj;
	}

	public static function create($count, $database = false) {
		$me = self::getInstance($database);
		$objectset = new DBObjectSet($database, $me->table);
		for ($i = 0; $i < $count; $i++) {
			$obj = new DBObject($database, $me->table);
			$me->applySchema($obj);
			$objectset[] = $obj;
		}
		return $me->applySchema($objectset);
	}

	public static function select(SQLQuery $query = null, $database = false) {
		$me = self::getInstance($database);
		if (!$query) {
			$query = SQLQuery::select($database);
			$query->from($me->table);
		}
		if (!$query->getDatabase()) {
			$query->setDatabase($database);
		}
		if(!$query->getTables()) {
			$query->from($me->table);
		}
		if ($query->getFields()) {
			$query->fields($me->primary_key);
		}
		$stmt = $query->prepare();
		$stmt->execute();
		$results = array();
		while ($result = $stmt->fetchAssoc()) {
			$results[] = new DBObject($database, $me->table, $result);
		}
		foreach ($results as $result) {
			$me->applySchema($result);
		}
		$objectset = new DBObjectSet($database, $me->table, $results);
		$me->applySchema($objectset);
		return $objectset;
	}

	protected function applySchema(&$obj, $relationships = array()) {
		$obj->setFields($this->fields);
		$obj->setPrimaryKey($this->primary_key);
		if(!empty($relationships)) {
			foreach($relationships as $k => $v) {
				if(isset($this->relationships[$k])) {
					$obj->addRelationship(new Relationship($v['type'], $v['key'], $v['foreign_key']));
				}
			}
		}
		return $obj;
	}

	/**
	 * @return DBObject
	 * Selects a single row from the database where column = value.
	 */
	public static function selectOne($column, $value, $relationships = array(), $database = false) {
		$me = self::getInstance($database);
		$q = SQLQuery::select($database);
		$q->from($me->table);
		$q->limit(1);
		$q->where("$column = '$value'");
		$stmt = $q->prepare();
		$stmt->execute();
		$result = $stmt->fetchAssoc();
		if($result) {
			$result = new DBObject($database, $me->table, $result);
			$me->applySchema($result, $relationships);
		}
		return $result;
	}

	/**
	 * Deletes a single row from the database where column = value.
	 * @return true on success, false on failure.
	 */
	public static function deleteOne($column, $value, $database = false) {
		$q = SQLQuery::delete($database);
		$q->from(self::getInstance()->table);
		$q->where("$column = '$value'");
		$stmt = $q->prepare();
		if ($stmt->execute()) {
			return true;
		}
		return false;
	}

	public function setTable($table) {
		$this->table = $table;
	}

	public function setDatabase($database) {
		$this->database = $database;
	}

	public function getFields() {
		return $this->fields;
	}

	public static function bindValidation($names, $input_array = 'POST') {
		if (!is_array($names)) {
			$names = array($names);
		}
		$me = self::getInstance();
		$rules = array();
		foreach ($names as $name) {
			if (isset($me->rules[$name])) {
				$rules[$name] = $me->rules[$name];
			}
		}
		$v = new Validator($input_array, $rules, $me->messages);
		return $v;
	}

	public static function buildForm($action, $values = array(), $errors = array(), $method = 'POST') {
		$me = self::getInstance();
		$f = Form::create($action, $method);
		foreach($me->fields as $field) {
			$f->add($field, 'text');
		}
		$f->add('submit', 'submit', 'Submit');
		$f->setType($me->primary_key, 'hidden');
		$f->set($values);
		$f->addErrors($errors);
		return $f;
	}

}

?>

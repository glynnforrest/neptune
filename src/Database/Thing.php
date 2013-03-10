<?php

namespace neptune\database;

use neptune\database\ThingCollection;
use neptune\database\SQLQuery;
use neptune\database\DatabaseFactory;
use neptune\database\relations\Relation;
use neptune\database\relations\RelationsManager;
use neptune\validate\Validator;
use neptune\view\Form;

/**
 * Thing
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Thing {

	protected static $table;
	protected static $fields = array();
	protected static $primary_key = 'id';
	protected static $relations = array();
	protected static $rules = array();
	protected static $messages = array();
	protected $database;
	protected $current_index;
	protected $values = array();
	protected $modified = array();
	protected $stored = false;
	protected $relation_objects = array();
	protected $relation_keys = array();
	protected $no_friends = array();

	public function __construct($database, array $result = null) {
		$this->database = $database;
		if ($result && is_array($result)) {
			foreach ($result as $key => $value) {
				$this->values[$key] = $value;
			}
			$this->stored = true;
		}
	}

	public function __get($key) {
		$method = 'get'.ucfirst($key);
		if(method_exists($this, $method)) {
			return $this->$method();
		}
		return $this->get($key);
	}

	public function get($key) {
		if (isset(static::$relations[$key])) {
			return $this->getRelation($key);
		}
		if (isset($this->values[$key])) {
			return $this->values[$key];
		}
		return null;
	}

	protected function getRelation($name) {
		if(isset($this->no_friends[$name])) {
			return null;
		}
		if(!isset($this->relation_objects[$name])) {
			RelationsManager::getInstance()->createRelation($this, $name,
			static::$relations[$name]);
		}
		$key = static::$relations[$name]['key'];
		return $this->relation_objects[$name]->getRelatedObject($key);
	}

	protected function setRelation($name, &$value) {
		if(!isset($this->relation_objects[$name])) {
			RelationsManager::getInstance()->createRelation($this, $name,
			static::$relations[$name], $value);
		}
		$key = static::$relations[$name]['key'];
		$this->no_friends[$name] = null;
		$this->relation_objects[$name]->setRelatedObject($key, $value)
			->updateKey($key, $this->get($key));
	}

	public function __set($key, $value) {
		$method = 'set'.ucfirst($key);
		if(method_exists($this, $method)) {
			return $this->$method($value);
		}
		return $this->set($key, $value);
	}

	public function set($key, $value, $overwrite = true) {
		if (isset(static::$relations[$key])) {
			return $this->setRelation($key, $value);
		}
		if($key === static::$primary_key && isset($this->values[$key])) {
			$this->current_index = $this->values[$key];
		}
		if($overwrite) {
			$this->setValue($key, $value);
		} else {
			if(!isset($this->values[$key])) {
				$this->setValue($key, $value);
			} else {
				return false;
			}
		}
		if (in_array($key, static::$fields) && !in_array($key, $this->modified)) {
			$this->modified [] = $key;
		}
	}

	protected function setValue($key, $value) {
		$this->values[$key] = $value;
		if(isset($this->relation_keys[$key])) {
			$this->relation_objects[$this->relation_keys[$key]]->updateKey($key, $value);
		}
	}

	public function setValues($values = array(), $overwrite = true) {
		foreach ($values as $k => $v) {
			$this->set($k, $v, $overwrite);
		}
		return $this;
	}

	public function noRelation($relation) {
		$this->no_friends[$relation] = true;
	}

	public function __isset($key) {
		return isset($this->values[$key]);
	}

	public function getValues() {
		return $this->values;
	}

	public function save() {
		if ($this->stored) {
			if (!empty($this->modified)) {
				return $this->update();
			} else {
				return true;
			}
		} else {
			return $this->insert();
		}
	}

	public function delete() {
		if (!isset(static::$primary_key)) {
			throw new \Exception('Can\'t update with no index key');
		}
		$q = SQLQuery::delete($this->database);
		$q->from($this->table);
		if (!isset($this->values[static::$primary_key])) {
			throw new \Exception('Can\'t update with no index key');
		}
		$q->where(static::$primary_key. " =", '?');
		$stmt = $q->prepare();
		if($this->current_index) {
			$index = $this->current_index;
		} else {
			$index = $this->values[static::$primary_key];
		}
		if ($stmt->execute(array($index))) {
			return true;
		}
		return false;
	}

	//TODO: add params to update on other fields.
	public function update() {
		if (!empty($this->modified)) {
			$q = SQLQuery::update($this->database);
			$q->tables(static::$table);
			$q->fields($this->modified);
			if (!isset($this->values[static::$primary_key])) {
				throw new \Exception('Can\'t update with no index key');
			}
			$q->where(static::$primary_key . " =", '?');
			$stmt = $q->prepare();
			$values = array();
			foreach ($this->modified as $modified) {
				$values[] = $this->values[$modified];
			}
			if($this->current_index) {
				$index = $this->current_index;
			} else {
				$index = $this->values[static::$primary_key];
			}
			$values[] = $index;
			if ($stmt->execute($values)) {
				$this->modified = array();
				return true;
			}
		}
		return false;
	}

	public function insert() {
		if (!empty($this->modified)) {
			$q = SQLQuery::insert($this->database);
			$q->into(static::$table);
			$values = array();
			foreach ($this->modified as $modified) {
				$values[] = $this->values[$modified];
			}
			$q->fields($this->modified);
			$stmt = $q->prepare();
			if ($stmt->execute($values)) {
				$this->modified = array();
				$this->stored = true;
				$this->set(static::$primary_key,
					DatabaseFactory::getDriver($this->database)->lastInsertId());
				return true;
			}
		}
		return false;
	}

	protected static function applySchema(&$obj, $relations = array()) {
		$obj->setFields(static::$fields);
		$obj->setPrimaryKey(static::$primary_key);
		$obj->setChildClass(get_called_class());
		return $obj;
	}

	public function addRelation($name, $key, Relation &$r) {
		$this->relation_objects[$name] = $r;
		$this->relation_keys[$key] = $name;
		$r->setObject($key, $this);
		$r->updateKey($key, $this->$key);
	}

	public static function getTable() {
		return static::$table;
	}

	public static function createOne($data = array(), $database = false) {
		return new static($database, $data);
	}

	public static function create($count, $database = false) {
		$set = new ThingCollection($database, self::$table);
		for ($i = 0; $i < $count; $i++) {
			$set[] = new static($database);
		}
		static::applySchema($set);
		return $set;
	}

	public static function selectOne($column, $value, $database = false) {
		$q = SQLQuery::select($database);
		$q->from(static::$table);
		$q->limit(1);
		$q->where("$column = '$value'");
		$stmt = $q->prepare();
		$stmt->execute();
		$result = $stmt->fetchAssoc();
		if($result) {
			$result = new static($database, $result);
		}
		return $result;
	}

	public static function selectPK($value, $database = false) {
		return self::selectOne(static::$primary_key, $value, $database);
	}

	public static function select(SQLQuery $query = null,
		$relations = array(), $database = false) {
		if (!$query) {
			$query = SQLQuery::select($database);
			$query->from(static::$table);
		}
		if (!$query->getDatabase()) {
			$query->setDatabase($database);
		}
		if(!$query->getTables()) {
			$query->from(static::$table);
		}
		if ($query->getFields()) {
			$query->fields(static::$primary_key);
		}
		$stmt = $query->prepare();
		$stmt->execute();
		$results = array();
		while ($result = $stmt->fetchAssoc()) {
			$obj = new static($database, $result);
			$results[] = $obj;
		}
		$set = new ThingCollection($database, static::$table, $results);
		static::applySchema($set);
		//hydrate objects if we have a result set
		if(count($set)) {
			$rm = RelationsManager::getInstance();
			$relations = (array) $relations;
			foreach ($relations as $r) {
				if(isset(static::$relations[$r])) {
					$rm->eagerLoad($set, $r, static::$relations[$r], $database);
				}
			}
		}
		return $set;
	}

	/**
	 * Deletes a single row from the database where column = value.
	 * @return true on success, false on failure.
	 */
	public static function deleteOne($column, $value, $database = false) {
		$q = SQLQuery::delete($database);
		$q->from(static::$table);
		$q->where("$column = '$value'");
		//todo use ?
		$stmt = $q->prepare();
		if ($stmt->execute()) {
			return true;
		}
		return false;
	}

	public function setDatabase($database) {
		$this->database = $database;
	}

	public static function bindValidation($names, $input_array = 'POST') {
		if (!is_array($names)) {
			$names = array($names);
		}
		$rules = array();
		foreach ($names as $name) {
			if (isset(static::$rules[$name])) {
				$rules[$name] = static::$rules[$name];
			}
		}
		$v = new Validator($input_array, $rules, static::$messages);
		return $v;
	}

	public static function buildForm($action = null, $values = array(), $errors = array(), $method = 'POST') {
		$f = Form::create($action, $method);
		foreach(static::$fields as $field) {
			$f->add($field, 'text');
		}
		$f->add('submit', 'submit', 'Submit');
		$f->setType(static::$primary_key, 'hidden');
		$f->setValues($values);
		$f->addErrors($errors);
		return $f;
	}

}

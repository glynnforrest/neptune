<?php

namespace Neptune\Database;

use Neptune\Database\ThingCollection;
use Neptune\Database\SQLQuery;
use Neptune\Database\DatabaseFactory;
use Neptune\Database\Relations\Relation;
use Neptune\Database\Relations\RelationsManager;
use Neptune\Validate\Validator;
use Neptune\View\Form;

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
	protected $values = array();
	protected $modified = array();
	protected $stored = false;
	protected $relation_objects = array();
	protected $relation_keys = array();
	protected $no_friends = array();

	/**
	 * Create a new Thing instance.
	 *
	 * @param string $database The name of the database instance to use.
	 * @param array $values Initial values to give the instance. Note
	 * that no set<Key> methods will be called. To apply these
	 * methods, call setValues($data) after creation.
	 */
	public function __construct($database, array $values = array()) {
		$this->database = $database;
		if(!empty($values)) {
			foreach ($values as $key => $value) {
				//don't call setRaw as we don't want the modified flag to be
				//set.
				$this->values[$key] = $value;
			}
			$this->stored = true;
		}
	}

	/**
	 * Convenience wrapper to set().
	 */
	public function __get($key) {
		return $this->get($key);
	}

	/**
	 * Get the value of $key. If the method get$key exists, the return
	 * value will be the output of calling this function.
	 *
	 * @param string $key The name of the key to get.
	 */
	public function get($key) {
		$method = 'get'.ucfirst($key);
		if(method_exists($this, $method)) {
			return $this->$method();
		}
		if (isset(static::$relations[$key])) {
			return $this->getRelation($key);
		}
		return $this->getRaw($key);
	}

	/**
	 * Get the value of $key. If $key doesn't exist, null will be returned.
	 *
	 * @param string $key The name of the key to get.
	 */
	public function getRaw($key) {
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
			RelationsManager::getInstance()
				->createRelation($this, $name, static::$relations[$name]);
		}
		$key = static::$relations[$name]['key'];
		return $this->relation_objects[$name]->getRelatedObject($key);
	}

	protected function setRelation($name, &$value) {
		if(!isset($this->relation_objects[$name])) {
			RelationsManager::getInstance()
				->createRelation($this, $name, static::$relations[$name], $value);
		}
		$key = static::$relations[$name]['key'];
		$this->no_friends[$name] = null;
		$this->relation_objects[$name]->setRelatedObject($key, $value)
									  ->updateKey($key, $this->get($key));
	}

	/**
	 * Convenience wrapper to set().
	 */
	public function __set($key, $value) {
		return $this->set($key, $value);
	}

	/**
	 * Set $key to $value. If the method set$key exists, $value will
	 * be the output of calling this function with $value as an
	 * argument.
	 *
	 * @param string $key The name of the key to set.
	 * @param mixed $value The value to set. This may a related object.
	 * @param bool $overwrite Whether to overwrite the value if it is
	 * already set. Setting this to false is useful in batch
	 * operations on groups of Things, where there is a chance of
	 * overwriting a change applied to a single Thing.
	 */
	public function set($key, $value, $overwrite = true) {
		$method = 'set'.ucfirst($key);
		if(method_exists($this, $method)) {
			return $this->$method($value);
		}
		if (isset(static::$relations[$key])) {
			return $this->setRelation($key, $value);
		}
		if($key === static::$primary_key && isset($this->values[$key])) {
			$this->current_index = $this->values[$key];
		}
		if($overwrite) {
			$this->setRaw($key, $value);
		} else {
			if(!isset($this->values[$key])) {
				$this->setRaw($key, $value);
			} else {
				return false;
			}
		}
	}

	public function setRaw($key, $value) {
		if (in_array($key, static::$fields) && !in_array($key, $this->modified)) {
			$this->modified [] = $key;
		}
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
				$id = DatabaseFactory::getDriver($this->database)->lastInsertId();
				$this->set(static::$primary_key, $id);
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

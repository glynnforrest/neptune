<?php

namespace neptune\model;

use neptune\model\ModelGroup;
use neptune\database\SQLQuery;
use neptune\database\DatabaseFactory;
use neptune\database\relations\Relation;
use neptune\database\relations\OneToOne;
use neptune\validate\Validator;
use neptune\cache\Cacheable;
use neptune\exceptions\TypeException;
use neptune\view\Form;

/**
 * DatabaseModel
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class DatabaseModel extends Cacheable {

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
	protected $stored_relations = array();
	protected $relation_keys = array();
	protected $eager;

	public function __construct($database, array $result = null) {
		$this->database = $database;
		if ($result && is_array($result)) {
			foreach ($result as $key => $value) {
				$this->values[$key] = $value;
			}
			//move to select
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
		//switch type, run appropriate function
	}

	protected function setRelation($name, &$value) {
		//switch type, run appropriate function
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
			$this->stored_relations[$this->relation_keys[$key]]->updateKey($key, $value);
		}
	}

	public function setValues($values = array(), $overwrite = true) {
		foreach ($values as $k => $v) {
			$this->set($k, $v, $overwrite);
		}
		return $this;
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
		return $obj;
	}

	public function addRelation($name, $key, Relation &$r) {
		$this->stored_relations[$name] = $r;
		$this->relation_keys[$key] = $name;
		$r->setObject($key, $this);
		$r->updateKey($key, $this->$key);
	}

	protected function hasOne($key, $other_key, $other_class) {
		$name = $this->current_relationship;
		if(is_object($other_class)) {
			//setting relationship
			$r = new OneToOne($key, get_class($this), $other_key,
			   	get_class($other_class));
			$r->setObject($other_key, $other_class);
			$this->addRelation($name, $key, $r);
		} else {
			//getting relationship
			if(!isset($this->stored_relations[$name])) {
				$this->addRelation($name, $key, new OneToOne(
					$key, get_class($this), $other_key, $other_class));
			}
		}
		if($this->eager) {
			return $this->stored_relations[$name];
		}
		return $this->stored_relations[$name]->getRelatedObject($key);
	}

	protected function belongsTo($key, $other_key, $other_class) {
		$name = $this->current_relationship;
		if(is_object($other_class)) {
			//setting relationship
			$r = new OneToOne($other_key, get_class($other_class), $key,
			   	get_class($this));
			$r->setObject($other_key, $other_class);
			$this->addRelation($name, $key, $r);
		} else {
			//getting relationship
			if(!isset($this->stored_relations[$name])) {
				$this->addRelation($name, $key, new OneToOne($other_key,
					$other_class, $key, get_class($this)));
			}
		}
		return $this->stored_relations[$name]->getRelatedObject($key);
	}

	public static function createOne($data = array(), $database = false) {
		return new static($database, $data);
	}

	public static function create($count, $database = false) {
		$set = new ModelGroup($database, $me->table);
		for ($i = 0; $i < $count; $i++) {
			$set[] = new static($database);
		}
		static::applySchema($set);
		return $set;
	}

	public static function selectOne($column, $value, $relations = array(), $database = false) {
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
		$set = new ModelGroup($database, static::$table, $results);
		static::applySchema($set);
		//hydrate objects
		$rm = RelationsManager::getInstance();
		$relations = (array) $relations;
		foreach ($relations as $r) {
			if(isset(static::$relations[$r])) {
				$rm->eagerLoad($set, static::$relations[$r]);
			}
		}
		// if(empty($set)) {
		// 	return $set;
		// }
		// $set[0]->enableEager();
		// foreach($relations as $r) {
		// 	$rel = $set[0]->$r;
		// 	if($rel) {
		// 		$related_set = $rel->eager($set);
		// 		var_dump($related_set);
		// 	}
		// }
		return $set;
	}

	public function disableEager() {
		$this->eager = false;
	}

	public function enableEager() {
		$this->eager = true;
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

	public function setTable($table) {
		$this->table = $table;
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

	public static function buildForm($action, $values = array(), $errors = array(), $method = 'POST') {
		$f = Form::create($action, $method);
		foreach(static::$fields as $field) {
			$f->add($field, 'text');
		}
		$f->add('submit', 'submit', 'Submit');
		$f->setType(static::$primary_key, 'hidden');
		$f->set($values);
		$f->addErrors($errors);
		return $f;
	}

}

?>

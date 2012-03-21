<?php

namespace neptune\model;

use neptune\model\ModelGroup;
use neptune\database\SQLQuery;
use neptune\database\DatabaseFactory;
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

	protected static $table;
	protected static $fields = array();
	protected static $primary_key = 'id';
	protected static $rules = array();
	protected static $messages = array();
	protected $database;
	protected $current_index;
	protected $values = array();
	protected $modified = array();
	protected $stored = false;
	protected $relationships = array();
	protected $relationship_keys = array();

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
		if(isset($this->relationships[$key])) {
			$name = array_search($key, $this->relationship_keys);
			return $this->relationships[$key]->getRelatedObject($name);
		}
		if (isset($this->values[$key])) {
			return $this->values[$key];
		}
		return null;
	}

	public function __set($key, $value) {
		$method = 'set'.ucfirst($key);
		if(method_exists($this, $method)) {
			return $this->$method($value);
		}
		return $this->set($key, $value);
	}

	public function set($key, $value, $overwrite = true) {
		// if(isset($this->relationships[$key])) {
		// 	$name = array_search($key, $this->relationship_keys);
		// 	$this->relationships[$key]->setRelatedObject($name, $value);
		// 	if(isset($this->values[$name])) {
		// 		$this->relationships[$key]->setKey($name, $this->values[$name]);
		// 	}
		// }
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
		if(isset($this->relationship_keys[$key])) {
			$this->relationships[$this->relationship_keys[$key]]->setKey($key, $value);
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

	public function addRelationship($name, $key, Relationship &$r) {
		$this->relationships[$name] = $r;
		$this->relationship_keys[$key] = $name;
		$r->setObject($key, $this);
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
		$q->where("static::$primary_key =", '?');
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
			echo '<pre>'; print_r($q->prepare()); echo '</pre>';
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
				return true;
			}
		}
		return false;
	}


	protected static function applySchema(&$obj, $relationships = array()) {
		$obj->setFields(static::$fields);
		$obj->setPrimaryKey(static::$primary_key);
		if(!empty($relationships)) {
			foreach($relationships as $k => $v) {
				if(isset(static::$relationships[$k])) {
					$obj->addRelationship(new Relationship($v['type'], $v['key'], $v['foreign_key']));
				}
			}
		}
		return $obj;
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

	public static function selectOne($column, $value, $relationships = array(), $database = false) {
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

	public static function select(SQLQuery $query = null, $database = false) {
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
			$results[] = new static($database, static::$table, $result);
		}
		$set = new ModelGroup($database, static::$table, $results);
		static::applySchema($set);
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

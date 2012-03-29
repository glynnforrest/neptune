<?php

namespace neptune\model;

use \Iterator;
use \ArrayAccess;
use \Countable;
use neptune\model\DatabaseModel;

/**
 * ModelGroup
 * @author Glynn Forrest me@glynnforrest.com
 * */
class ModelGroup implements Iterator, ArrayAccess, Countable {

	protected $database;
	protected $table;
	protected $primary_key;
	protected $fields = array();
	protected $values = array();
	protected $objects = array();
	protected $modified = array();
	protected $stored = false;
	protected $position = 0;
	protected $child_class;

	public function __construct($database, $table, array $dbobjects = null) {
		$this->database = $database;
		$this->table = $table;
		if ($dbobjects) {
			$this->objects = $dbobjects;
			$this->stored = true;
		}
	}

	public function save($recursive = false) {
		if ($this->stored) {
			if (!empty($this->modified)) {
				$pass = $this->update();
			} else {
				$pass = true;
			}
		} else {
			if($recursive) {
				foreach ($this->objects as $obj) {
					foreach($this->modified as $value) {
						$obj->set($value, $this->values[$value], false);
					}
				}
				$pass = true;
			} else {
				$pass = $this->insert();
			}
		}
		if ($recursive) {
			foreach ($this->objects as $obj) {
				$obj->save();
			}
		}
		return $pass;
	}

	protected function insert() {
		if (!empty($this->modified)) {
			$q = SQLQuery::insert($this->database);
			$q->into($this->table);
			$q->fields($this->modified);
			$values = array();
			foreach ($this->modified as $modified) {
				$values[] = $this->values[$modified];
			}
			$stmt = $q->prepare();
			foreach ($this->objects as $obj) {
				if (!$stmt->execute($values)) {
					return false;
				}
			}
			$this->modified = array();
				$this->modified = array();
				$this->stored = true;
				return true;
		}
		return false;
	}

	protected function update() {
		if (!isset($this->primary_key)) {
			throw new \Exception('Can\'t update with no index key');
		}
		if (!empty($this->modified)) {
			$q = SQLQuery::update($this->database);
			$q->tables($this->table);
			$q->fields($this->modified);
			$values = array();
			foreach ($this->modified as $modified) {
				$values[] = $this->values[$modified];
			}
			$q->where("$this->primary_key =", '?');
			$stmt = $q->prepare();
			foreach ($this->objects as $obj) {
				$key = $this->primary_key;
				if (!isset($obj->$key)) {
					continue;
				}
				$params = $values;
				$params[] = $obj->$key;
				if (!$stmt->execute($params)) {
					return true;
				}
			}
			$this->modified = array();
		}
		return false;
	}

	public function __get($key) {
		return $this->get($key);
	}

	public function get($key) {
		$values = array();
		foreach ($this->objects as $obj) {
			$values[] = $obj->$key;
		}
		return $values;
	}

	public function __set($key, $value) {
		return $this->set($key, $value);
	}

	public function set($key, $value) {
		$this->values[$key] = $value;
		if (in_array($key, $this->fields) && !in_array($key, $this->modified)) {
			$this->modified [] = $key;
		}
	}

	public function setFields(array $fields) {
		$this->fields = $fields;
	}

	public function setPrimaryKey($columnname) {
		$this->primary_key = $columnname;
	}

	public function setChildClass($class) {
		$this->child_class = $class;
	}

	public function getChildClass() {
		return $this->child_class;
	}

	public function getValues() {
		$values = array();
		foreach ($this->objects as $obj) {
			$values[] = $obj->getValues();
		}
		return $values;
	}

	public function getTable() {
		return $this->table;
	}

	public function rewind() {
		$this->position = 0;
	}

	public function current() {
		return $this->objects[$this->position];
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		$this->position++;
	}

	public function valid() {
		return isset($this->objects[$this->position]);
	}

	public function offsetGet($offset) {
		return isset($this->objects[$offset]) ? $this->objects[$offset] : null;
	}

	public function offsetSet($offset, $value) {
		if (!$value instanceof DatabaseModel) {
			return false;
		}
		if (is_null($offset)) {
			$this->objects[] = $value;
		} else {
			$this->objects[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->objects[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->objects[$offset]);
	}

	public function count() {
		return count($this->objects);
	}

}

?>

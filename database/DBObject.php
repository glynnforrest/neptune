<?php

namespace neptune\database;

use neptune\database\SQLQuery;

/**
 * DBObject
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class DBObject {

	protected $database;
	protected $table;
	protected $primary_key;
	protected $current_index;
	protected $fields = array();
	protected $fieldmap = array();
	protected $values = array();
	protected $modified = array();
	protected $stored = false;

	public function __construct($database, $table, array $resultset = null) {
		$this->database = $database;
		$this->table = $table;
		if ($resultset && is_array($resultset)) {
			foreach ($resultset as $key => $value) {
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
		if($key === $this->primary_key && isset($this->values[$key])) {
			$this->current_index = $this->values[$key];		
		}
		if($overwrite) {
			$this->values[$key] = $value;
		} else {
			if(!isset($this->values[$key])) {
				$this->values[$key] = $value;
			} else {
				return false;
			}
		}
		if (in_array($key, $this->fields) && !in_array($key, $this->modified)) {
			$this->modified [] = $key;
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

	public function getTable() {
		return $this->table;
	}

	public function setFields(array $fields) {
		$this->fields = $fields;
	}

	public function setPrimaryKey($columnname) {
		$this->primary_key = $columnname;
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
		if (!isset($this->primary_key)) {
			throw new \Exception('Can\'t update with no index key');
		}
		$q = SQLQuery::delete($this->database);
		$q->from($this->table);
		if (!isset($this->values[$this->primary_key])) {
			throw new \Exception('Can\'t update with no index key');
		}
		$q->where("$this->primary_key =", '?');
		$stmt = $q->prepare();
		if($this->current_index) {
			$index = $this->current_index;
		} else {
			$index = $this->values[$this->primary_key];
		}
		if ($stmt->execute(array($index))) {
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
			if (!isset($this->values[$this->primary_key])) {
				throw new \Exception('Can\'t update with no index key');
			}
			$q->where("$this->primary_key =", '?');
			$stmt = $q->prepare();
			$values = array();
			foreach ($this->modified as $modified) {
				$values[] = $this->values[$modified];
			}
			if($this->current_index) {
				$index = $this->current_index;
			} else {
				$index = $this->values[$this->primary_key];
			}
			$values[] = $index;
			if ($stmt->execute($values)) {
				$this->modified = array();
				return true;
			}
		}
		return false;
	}

	protected function insert() {
		if (!empty($this->modified)) {
			$q = SQLQuery::insert($this->database);
			$q->into($this->table);
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

}
?>

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
	protected $values = array();
	protected $modified = array();
	protected $stored = false;
	protected $relations = array();
	protected $relation_keys = array();
	protected $relation_objects = array();

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
		if(array_key_exists($key, $this->relations)) {
			return $this->getRelationObject($key);
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
		if(array_key_exists($key, $this->relations)) {
			return $this->setRelationObject($key, $value);
		}
		if($key === $this->primary_key && isset($this->values[$key])) {
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
		if (in_array($key, $this->fields) && !in_array($key, $this->modified)) {
			$this->modified [] = $key;
		}		
	}

	protected function setValue($key, $value) {
			$this->values[$key] = $value;
			if(isset($this->relation_keys[$key])) {
				$this->updateRelation($this->relation_keys[$key]);
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

	public function setRelation($key, $relation) {
		$this->relations[$key] = $relation;
	}

	public function updateRelation($name) {
		$rm = RelationsManager::getInstance();
		$related = $this->relation_objects[$name];
		$rm->updateRelation($this, $related, $this->relations[$name]);
	}

	public function getRelation($key) {
		return isset($this->relations[$key]) ?: null;
	}

	public function setRelationObject($name, &$object) {
		if(!isset($this->relations[$name])) {
			return false;
		}
		$this->relation_objects[$name] = $object;
		$key = $this->relations[$name]['key'];
		$this->relation_keys[$key] = $name;
		$this->updateRelation($name);
	}

	public function getRelationObject($key) {
		//if we've got it, don't query it again
		if(isset($this->relation_objects[$key])) {
			return $this->relation_objects[$key];
		}
		$rm = RelationsManager::getInstance();
		if($rm->processRelation($this, $this->relations[$key])) {
			return $this->relation_objects[$key];
		}
		return null;
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

	public function setRelations(array $relations) {
		$this->relations = $relations;
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

	//TODO: add params to update on other fields.
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

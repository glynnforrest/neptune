<?php

namespace Neptune\Database\Entity;

use \Iterator;
use \ArrayAccess;
use \Countable;
use Neptune\Database\Entity\Entity;
use Neptune\Database\Entity\AbstractEntity;
use Neptune\Database\Driver\DatabaseDriverInterface;

/**
 * EntityCollection
 * @author Glynn Forrest me@glynnforrest.com
 * */
class EntityCollection extends AbstractEntity implements Iterator, ArrayAccess, Countable {

	protected $database;
	protected $table;
	protected $primary_key;
	protected $fields = array();
	protected $objects = array();
	protected $position = 0;
	protected $thing_class;

	public function __construct(DatabaseDriverInterface $database, array $objects = array()) {
		$this->database = $database;
        $this->objects = $objects;
	}

	public function __toString() {
		return get_class($this) . ' with ' . count($this->objects) . ' objects.';
	}

	public function save($iterate = false) {
		if ($iterate) {
			foreach ($this->objects as $obj) {
				$obj->save();
			}
            return true;
		}

		if ($this->stored) {
            return $this->update();
		}
        return $this->insert();
	}

	protected function insert() {
		if (empty($this->modified)) {
            return true;
		}
        $q = $this->database->insert();
        $q->into($this->table);
        $q->fields($this->modified);
        $values = array();
        foreach ($this->modified as $modified) {
            $values[] = $this->values[$modified];
        }
        $stmt = $q->prepare();
        foreach ($this->objects as $obj) {
            $stmt->execute($values);
        }
        $this->modified = array();
        $this->stored = true;
        return true;
	}

	protected function update() {
		if (empty($this->modified)) {
            return true;
        }
        if (!isset($this->primary_key)) {
            throw new \Exception('Can\'t update with no index key');
        }
        $q = $this->database->update();
        $q->tables($this->table);
        $q->fields($this->modified);
        $values = array();
        foreach ($this->modified as $modified) {
            $values[] = $this->values[$modified];
        }
        $q->where("$this->primary_key =", '?');
        $stmt = $q->prepare();
        foreach ($this->objects as $obj) {
            $key = $obj->get($this->primary_key);
            if ($key === null) {
                continue;
            }
            $params = $values;
            $params[] = $key;
            $stmt->execute($params);
        }
        $this->modified = array();
	}

	public function get($key, $array = false) {
        if (!$array) {
            return parent::get($key);
        }
		$values = array();
		foreach ($this->objects as $obj) {
			$values[] = $obj->get($key);
		}
		return $values;
	}

    public function getRaw($key, $array = false)
    {
		if (isset($this->values[$key])) {
			return $this->values[$key];
		}
		return null;
    }

	public function set($key, $value, $iterate = false) {
        return parent::set($key, $value);
	}

    public function setRaw($key, $value, $iterate = false)
    {
        //Updating the primary key on a set of objects is not allowed
		if($key === $this->primary_key) {
            throw new \InvalidArgumentException("Updating the primary key of a EntityCollection is not allowed.");
        }
        //maybe apply the modified flag
		if (in_array($key, $this->fields) && !in_array($key, $this->modified)) {
			$this->modified [] = $key;
		}
		$this->values[$key] = $value;
    }

    public function setTable($table)
    {
		$this->table = $table;
    }

	public function setFields(array $fields) {
		$this->fields = $fields;
	}

	public function setPrimaryKey($columnname) {
		$this->primary_key = $columnname;
	}

	public function setEntityClass($class) {
		$this->thing_class = $class;
	}

	public function getEntityClass() {
		return $this->thing_class;
	}

	public function getValues($iterate = false) {
        return parent::getValues();
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
		if (!$value instanceof Entity) {
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

	public function getLast() {
		return end($this->objects);
	}

	public function getFirst() {
		return $this->objects[0];
	}

}

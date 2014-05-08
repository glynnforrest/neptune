<?php

namespace Neptune\Database\Entity;

use Neptune\Database\Entity\EntityCollection;
use Neptune\Database\Driver\DatabaseDriverInterface;
use Neptune\Database\DatabaseFactory;
use Neptune\Database\Entity\AbstractEntity;
use Neptune\Database\Query\AbstractQuery;

/**
 * Entity
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Entity extends AbstractEntity {

	protected static $table;
	protected static $fields = array();
	protected static $primary_key = 'id';
	protected static $relations = array();
	protected $current_index;

	/**
	 * Create a new Entity instance.
	 *
	 * @param string $database The name of the database instance to use.
	 * @param array $values Initial values to give the instance. Note
	 * that no set<Key> methods will be called. To apply these
	 * methods, call setValues($data) after creation.
	 */
	public function __construct(DatabaseDriverInterface $database, array $values = array()) {
		$this->database = $database;
        foreach ($values as $key => $value) {
            //don't call setRaw as we don't want the modified flag to be
            //set.
            $this->values[$key] = $value;
        }
	}

    public function __sleep()
    {
        return array('values', 'modified', 'stored', 'current_index', 'relation_objects', 'relation_keys');
    }

    public function setRaw($key, $value)
    {
		if (isset(static::$relations[$key])) {
			return $this->setRelation($key, $value);
		}
		//if attempting to update the primary key, keep a copy of the current
		if($key === static::$primary_key && isset($this->values[$key])) {
			$this->current_index = $this->values[$key];
		}
		//maybe apply the modified flag
		if (in_array($key, static::$fields) && !in_array($key, $this->modified)) {
			$this->modified [] = $key;
		}
		$this->values[$key] = $value;
    }

	public function getRaw($key) {
		if (isset(static::$relations[$key])) {
			return $this->getRelation($key);
		}
		if (isset($this->values[$key])) {
			return $this->values[$key];
		}
		return null;
	}

	public function save() {
		if ($this->stored) {
            return $this->update();
		}
        return $this->insert();
	}

	public function delete() {
		if (!isset(static::$primary_key)) {
			throw new \Exception('Can\'t delete with no index key');
		}
		$q = $this->database->delete();
		$q->from(static::$table);
		if (!isset($this->values[static::$primary_key])) {
			throw new \Exception('Can\'t delete with no index key');
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

	public function update() {
		if (empty($this->modified)) {
            return true;
        }
        if (!isset($this->values[static::$primary_key])) {
            throw new \Exception('Can\'t update with no index key');
        }
        $q = $this->database->update();
        $q->tables(static::$table);
        $q->fields($this->modified);
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
    return false;
}

	public function insert() {
		if (empty($this->modified)) {
            return true;
		}
        $q = $this->database->insert();
        $q->into(static::$table);
        $values = array();
        foreach ($this->modified as $modified) {
            if($this->values[$modified]) {
                $values[] = $this->values[$modified];
            } else {
                $values[] = '';
            }
        }
        $q->fields($this->modified);
        $stmt = $q->prepare();
        if ($stmt->execute($values)) {
            $this->modified = array();
            $this->stored = true;
            $id = $this->database->lastInsertId();
            $this->set(static::$primary_key, $id);
            return true;
        }
		return false;
	}

	public static function getTable() {
        return static::$table;
	}

    public static function getFields()
    {
        return static::$fields;
    }

    protected static function newCollection(DatabaseDriverInterface $database, array $objects) {
        return new EntityCollection($database, $objects);
    }

	public static function collection(DatabaseDriverInterface $database, $count = 0) {
        $objects = array();
		for ($i = 0; $i < (int) $count; $i++) {
            $obj = new static($database);
            $obj->setStored();
			$objects[] = $obj;
		}
		$set = static::newCollection($database, $objects);
		$set->setTable(static::$table);
		$set->setFields(static::$fields);
		$set->setPrimaryKey(static::$primary_key);
		$set->setEntityClass(get_called_class());
		return $set;
	}

	public static function selectOne(DatabaseDriverInterface $database, $column, $value) {
		$q = $database->select()
                      ->from(static::$table)
                      ->limit(1)
                      ->where("$column = '$value'");
		$stmt = $q->prepare();
		$stmt->execute();
		$result = $stmt->fetchAssoc();
		if($result) {
			$result = new static($database, $result);
            $result->setStored();
		}
		return $result;
	}

	public static function selectPK(DatabaseDriverInterface $database, $value) {
		return self::selectOne($database, static::$primary_key, $value);
	}

	public static function select(DatabaseDriverInterface $database) {
        return new EntityBuilder($database->select(), get_called_class());
	}

    public static function selectFromQuery(AbstractQuery $query, array $relations = array())
    {
		if (!$query->getTables()) {
			$query->from(static::$table);
		}
		if ($query->getFields()) {
			$query->fields(static::$primary_key);
		}
		$stmt = $query->prepare();
		$stmt->execute();
		$results = array();
        $database = $query->getDatabase();
		while ($result = $stmt->fetchAssoc()) {
			$obj = new static($database, $result);
			$results[] = $obj;
		}
		$set = static::collection($database);
        $set->setEntities($results);
		//hydrate objects if we have a result set
		if(count($set)) {
			$rm = $database->getRelationManager();
			$relations = (array) $relations;
			foreach ($relations as $r) {
				if(isset(static::$relations[$r])) {
					$rm->eagerLoad($set, $r, static::$relations[$r]);
				}
			}
		}
		return $set;
    }

	/**
	 * Delete a single row from the database where $column = $value.
	 *
	 * @param string $column The column name.
	 * @param string $value The value of the column.
	 * @return bool true on success, false on failure.
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

    /**
     * Delete all records from the Entity table.
     *
     * @param DatabaseDriverInterface $database
     */
    public static function deleteAll(DatabaseDriverInterface $database)
    {
        $q = $database->delete()->from(static::$table);
        $stmt = $q->prepare();

        return $stmt->execute();
    }

}

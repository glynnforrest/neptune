<?php

namespace Neptune\Database\Relation;

use Neptune\Database\Driver\DatabaseDriverInterface;

/**
 * RelationManager
 * @author Glynn Forrest me@glynnforrest.com
 **/
class RelationManager {

    protected $database;

	public function __construct(DatabaseDriverInterface $database) {
        $this->database = $database;
	}

	public function eagerLoad(&$collection, $name, $relation, $database = false) {
		$key = $relation['key'];
		$other_key = $relation['other_key'];
		$other_class = $relation['other_class'];
		$q = $this->database->select()
                            ->from($other_class::getTable())
                            ->whereIn($other_key, $collection->get($key));
		$stmt = $q->prepare();
		$stmt->execute();
		$results = array();
		while($res = $stmt->fetchAssoc()) {
			$results[$res[$other_key]] = $res;
		}
		foreach($collection as $obj) {
			if(isset($results[$obj->$key])) {
				$related = new $other_class($database, $results[$obj->$key]);
				$this->createRelation($obj, $name, $relation, $related);
			} else {
				$obj->noRelation($name);
			}
		}
	}

    /**
     * Create a new Relation that matches a given configuration.
     */
	public function createRelation($calling_class, array $relation) {
		$type = $relation['type'];
		$key = $relation['key'];
		$other_key = $relation['other_key'];
		$other_class = $relation['other_class'];
		switch ($type) {
			case 'has_one':
                return new OneToOne($this->database, $key, $calling_class, $other_key, $other_class);
			case 'belongs_to':
                return new OneToOne($this->database, $other_key, $other_class, $key, $calling_class);
			case 'has_many':
                return new OneToMany($this->database, $key, $calling_class, $other_key, $other_class);
			default:
                throw new \InvalidArgumentException("Invalid relation type: $type");
		}
	}

}
?>

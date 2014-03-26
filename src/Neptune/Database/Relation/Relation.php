<?php

namespace Neptune\Database\Relation;

use Neptune\Database\Driver\DatabaseDriverInterface;
use Neptune\Database\Entity\AbstractEntity;

/**
 * Relation
 * @author Glynn Forrest me@glynnforrest.com
 **/
abstract class Relation {

    protected $database;
	protected $left;
	protected $right;
	protected $left_key;
	protected $right_key;
	protected $left_class;
	protected $right_class;

	public function __construct(DatabaseDriverInterface $database, $left_key, $left_class, $right_key, $right_class) {
        $this->database = $database;
		$this->left_key = $left_key;
		$this->right_key = $right_key;
		$this->left_class = $left_class;
		$this->right_class = $right_class;
	}

	public function setObject($class, AbstractEntity $object) {
        if ($class === $this->left_class) {
            $this->left = $object;
            return $this;
        }
        if ($class === $this->right_class) {
            $this->right = $object;
            return $this;
        }
        throw new \InvalidArgumentException("$class is not a part of this relation between {$this->left_class} and {$this->right_class}");
	}

	public function setRelatedObject($calling_class, AbstractEntity $related_object) {
        if ($calling_class === $this->left_class) {
            $this->right = $related_object;
            $this->updateKey($this->left_class);
            return $this;
        }
        if ($calling_class === $this->right_class) {
            $this->left = $related_object;
            //add an internal relation for the left side object, so it
            //can notify the right when its key changes.
            $this->updateKey($this->left_class);
            $related_object->addRelation('_' . $this->right_key, $this);
            return $this;
        }
        throw new \InvalidArgumentException("$calling_class is not a part of this relation {$this->left_class}, {$this->right_class}");
	}

	public function getRelatedObject($calling_class) {
        if ($calling_class === $this->left_class) {
            return $this->right();
        }
        if ($calling_class === $this->right_class) {
            return $this->left();
        }
        throw new \InvalidArgumentException("$calling_class is not a part of this relation {$this->left_class}, {$this->right_class}");
	}

	abstract protected function left();

	abstract protected function right();

    public function getKey($class)
    {
        if ($class === $this->left_class) {
            return $this->left_key;
        }
        if ($class === $this->right_class) {
            return $this->right_key;
        }
        throw new \InvalidArgumentException("$class is not a part of this relation {$this->left_class}, {$this->right_class}");
    }

    /**
     * Update the right hand object key so it matches the left.
     */
	public function updateKey($calling_class) {
        if (!$this->left || !$this->right || $calling_class !== $this->left_class) {
            return false;
        }
        $value = $this->left->get($this->left_key);
        $this->right->setRaw($this->right_key, $value);
        return true;
	}

}

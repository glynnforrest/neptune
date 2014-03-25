<?php

namespace Neptune\Database\Driver;

use \PDO;
use Neptune\Database\Relation\RelationManager;

/**
 * PDODriver
 * @author Glynn Forrest me@glynnforrest.com
 **/
class PDODriver implements DatabaseDriverInterface
{

    protected $pdo;
    protected $query_class;
    protected $relations_manager;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function prepare($query, $options = array())
    {
        return $this->pdo->prepare($query, $options);
    }

    public function quote($string)
    {
        return $this->pdo->quote($string);
    }

    public function getQueryClass()
    {
        return $this->query_class;
    }

    public function setQueryClass($class)
    {
        $this->query_class = $class;
        return $this;
    }

    public function lastInsertId($column = null)
    {
        return $this->pdo->lastInsertId();
    }

    public function select()
    {
        $class = $this->query_class;

        return new $class($this, 'SELECT');
    }

    public function insert()
    {
        $class = $this->query_class;

        return new $class($this, 'INSERT');
    }

    public function update()
    {
        $class = $this->query_class;

        return new $class($this, 'UPDATE');
    }

    public function delete()
    {
        $class = $this->query_class;

        return new $class($this, 'DELETE');
    }

    public function getRelationManager()
    {
        if (!$this->relations_manager) {
            $this->relations_manager = new RelationManager($this);
        }

        return $this->relations_manager;
    }

}

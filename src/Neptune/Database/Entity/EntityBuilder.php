<?php

namespace Neptune\Database\Entity;

use Neptune\Database\Query\AbstractQuery;

/**
 * EntityBuilder
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EntityBuilder
{

    protected $query;
    protected $thing_class;
    protected $eager = array();

    public function __construct(AbstractQuery $query, $thing_class)
    {
        if ($query->getType()  !== 'SELECT') {
            throw new \InvalidArgumentException("EntityBuilder only supports SELECT queries.");
        }
        $this->query = $query;
        $this->thing_class = $thing_class;
    }

    public function __call($method, array $args)
    {
        call_user_func_array(array($this->query, $method), $args);
        return $this;
    }

    public function execute()
    {
        $class = $this->thing_class;
        return $class::selectFromQuery($this->query, $this->eager);
    }

    public function eager($relations)
    {
        $this->eager = (array) $relations;
    }

}

<?php

namespace Neptune\Database\Statement;

/**
 * QueryStatement
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class QueryStatement
{

    protected $statement;
    protected $params = array();
    protected $expected_params;

    public function __construct(\PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->statement, $method), $args);
    }

    public function setParameters(array $params = array())
    {
        $this->params = $params;
    }

    public function getParameters()
    {
        return $this->params;
    }

    public function setExpectedParameters(array $params = array())
    {
        $this->expected_params = $params;
    }

    public function getExpectedParameters()
    {
        return $this->expected_params;
    }

    public function execute(array $params = array())
    {
        if (!$this->expected_params) {
            return $this->statement->execute(array_merge($this->params, $params));
        }
        $values = $this->params;
        foreach ($this->expected_params as $index) {
            $values[$index] = array_shift($params);
        }

        return $this->statement->execute(array_merge($values, $params));
    }

    public function fetch()
    {
        return $this->statement->fetch();
    }

    public function fetchAssoc()
    {
        return $this->statement->fetch(\PDO::FETCH_ASSOC);
    }

}

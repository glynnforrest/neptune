<?php

namespace Neptune\Database\Query;

use Neptune\Database\Driver\DatabaseDriverInterface;

/**
 * AbstractQuery
 * @author Glynn Forrest <me@glynnforrest.com>
 */
abstract class AbstractQuery {

	protected $query = array();
	protected $type;
	protected $select_verbs = array(
        'DISTINCT',
        'FIELDS',
        'FROM',
        'JOIN',
        'WHERE',
        'ORDER BY',
        'LIMIT',
        'OFFSET'
    );
	protected $insert_verbs = array('INTO', 'FIELDS');
	protected $update_verbs = array('TABLES', 'FIELDS', 'WHERE');
	protected $delete_verbs = array('FROM', 'WHERE');
	protected $driver;
    protected $params = array();
    protected $expected_params = array();
    protected $param_count = 0;

	public function __construct(DatabaseDriverInterface $driver, $type) {
		$this->driver = $driver;
        if (!in_array($type, array('SELECT', 'INSERT', 'UPDATE', 'DELETE'))) {
            throw new \Exception("Unknown query type $this->type");
        }
		$this->type = $type;
	}

    public function getType()
    {
        return $this->type;
    }

	public function getSQL() {
		switch ($this->type) {
			case 'SELECT':
				return $this->getSelectSQL();
			case 'INSERT':
				return $this->getInsertSQL();
			case 'UPDATE':
				return $this->getUpdateSQL();
			case 'DELETE':
				return $this->getDeleteSQL();
        default:
            return null;
		}
	}

    /**
     * Add a value onto the array of parameters that will given when
     * executing this query.
     */
    protected function addParameter($value)
    {
        $this->params[] = $value;
        $this->param_count++;
    }

    public function getParameters()
    {
        return $this->params;
    }

    /**
     * Look at a SQL expression for any ? placeholders. If so, take note
     * of these and expect these parameters when the query is
     * prepared.
     * @param $expression The SQL expression to examine
     */
    protected function maybeAddExpected($expression)
    {
        $placeholders = substr_count($expression, '?');
        for ($i = 0; $i < $placeholders; $i++) {
            $this->params[] = null;
            $this->expected_params[] = $this->param_count;
            $this->param_count++;
        }
    }

    public function getExpectedParameters()
    {
        return $this->expected_params;
    }

	protected abstract function getSelectSQL();

	protected abstract function getInsertSQL();

	protected abstract function getUpdateSQL();

	protected abstract function getDeleteSQL();

	public function fields($fields) {
		if (!is_array($fields)) {
			$fields = array($fields);
		}
		if (isset($this->query['FIELDS'])) {
			$this->query['FIELDS'] = array_merge($this->query['FIELDS'], $fields);
		} else {
			$this->query['FIELDS'] = $fields;
		}
		return $this;
	}

	public function from($tables) {
		if (!is_array($tables)) {
			$tables = array($tables);
		}
		if (isset($this->query['FROM'])) {
			$this->query['FROM'] = array_merge($this->query['FROM'], $tables);
		} else {
			$this->query['FROM'] = $tables;
		}
		return $this;
	}

	public function into($table) {
		$this->query['INTO'] = $table;
		return $this;
	}

	public function tables($tables) {
		if (!is_array($tables)) {
			$tables = array($tables);
		}
		if (isset($this->query['TABLES'])) {
			$this->query['TABLES'] = array_merge($this->query['TABLES'], $tables);
		} else {
			$this->query['TABLES'] = $tables;
		}
		return $this;
	}

	public function where($expression, $value = null, $logic='AND') {
        $logic = strtoupper($logic);
        //if value if null, there is a complete where expression supplied.
        $this->maybeAddExpected($expression);
        if (is_null($value)) {
            return $this->addElement('WHERE', array($expression, false, $logic));
        }
        $this->addParameter($value);
        return $this->addElement('WHERE', array($expression, true, $logic));
        /* if ($value && empty($value) && strlen($value) === 0) { */
        /*     return $this; */
        /* } */
	}

    protected function addElement($type, $element)
    {
        if (!isset($this->query[$type])) {
			$this->query[$type] = array();
		}
        $this->query[$type][] = $element;
        return $this;
    }

	public function andWhere($comparison, $value) {
		return $this->where($comparison, $value, 'AND');
	}

	public function orWhere($comparison, $value) {
		return $this->where($comparison, $value, 'OR');
	}

	public function whereIn($column, $values, $logic='AND') {
		$values = (array) $values;
		foreach($values as $v) {
			$v = $this->driver->quote($v);
		}
		$string = $column . ' IN (' . implode(',', $values) . ')';
		return $this->where($string, null, $logic);
	}

	public function andWhereIn($column, $values) {
		return $this->whereIn($column, $values, 'AND');
	}

	public function orWhereIn($column, $values) {
		return $this->whereIn($column, $values, 'OR');
	}

	public function orderBy($expression, $sort = 'ASC') {
		$sort = strtoupper($sort);
		if ($sort !== 'DESC') {
			$sort = 'ASC';
		}
		if (!isset($this->query['ORDER BY'])) {
			$this->query['ORDER BY'] = array();
		}
		$this->query['ORDER BY'][] = array($expression, $sort);
		return $this;
	}

	//todo - maybe add second argument that calls offset
	public function limit($int) {
		$this->query['LIMIT'] = $int;
		return $this;
	}

	public function offset($int) {
		$this->query['OFFSET'] = $int;
		return $this;
	}

	public function distinct() {
		$this->query['DISTINCT'] = true;
		return $this;
	}

	public function join($table, $type = null) {
		$this->query['JOIN'] = $table;
		return $this;
	}

	public function getTables() {
		switch ($this->type) {
			case 'SELECT':
				return isset($this->query['FROM']) ? $this->query['FROM'] : null;
			case 'INSERT':
				return isset($this->query['INTO']) ? array($this->query['INTO']) : null;
			case 'UPDATE':
				return isset($this->query['TABLES']) ? $this->query['TABLES'] : null;
			case 'DELETE':
				return isset($this->query['FROM']) ? $this->query['FROM'] : null;
			default:
				return null;
		}
	}

	public function getFields() {
		if (isset($this->query['FIELDS'])) {
			return $this->query['FIELDS'];
		}
		return false;
	}

	public function setDatabase(DatabaseDriverInterface $database) {
		$this->driver = $database;
        return $this;
	}

	public function getDatabase() {
		return $this->driver;
	}

	public function prepare() {
        $query = $this->getSQL();
        $statement = $this->driver->prepare($query);
        $statement->setParameters($this->getParameters());
        $statement->setExpectedParameters($this->expected_params);
        return $statement;
	}

    /**
     * Add a value to be bound to the statement when the query is prepared.
     */
    protected function addBind($value)
    {

    }

	public function __toString() {
		return $this->getSQL();
	}

}

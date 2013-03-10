<?php

namespace Neptune\Database\Drivers;

use Neptune\Database\Drivers\DatabaseDriver;
use Neptune\Database\Statements\DebugStatement;
use Neptune\Core\Events;

/**
 * DebugDriver
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class DebugDriver implements DatabaseDriver {

	protected $builder = '\\Neptune\\Database\\Builders\\GenericSQLBuilder';
	protected $query;
	protected $statement;

	public function __construct($host, $port, $user, $pass, $db) {
	}

	public function prepare($query) {
		Events::getInstance()->send('neptune.query', $query);
		$this->query = $query;
		$this->statement = new DebugStatement($query);
		return $this->statement;
	}

	public function quote($string) {
		return '\'' . addslashes($string) . '\'';
	}

	public function getBuilderName() {
		return $this->builder;
	}

	public function setBuilderName($builder) {
		$this->builder = $builder;
	}

	public function reset() {
		$this->query = null;
		$this->statement = null;
	}

	public function getPreparedQuery() {
		return $this->query;
	}

	public function getExecutedQuery() {
		return $this->statement ? $this->statement->getExecutedQuery() : null;
	}

	public function lastInsertId($column = null) {
		return true;
	}

}
?>

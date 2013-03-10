<?php

namespace Neptune\Database\Drivers;

use \PDO;
use Neptune\Database\Statements\GenericStatement;
use Neptune\Core\Events;

/**
 * MysqlDriver
 * @author Glynn Forrest me@glynnforrest.com
 **/
class MysqlDriver implements DatabaseDriver {

	protected $builder = '\\Neptune\\Database\\Builders\\GenericSQLBuilder';
	protected $pdo;

	public function __construct($host, $port, $user, $pass, $db) {
		$dsn = "mysql:host=$host;port=$port;dbname=$db";
		$options = array(
			 PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);
		$this->pdo = new PDO($dsn, $user, $pass, $options);
	}

	public function prepare($query, $options = array()) {
		Events::getInstance()->send('neptune.query', $query);
		return new GenericStatement($this->pdo->prepare($query, $options));
	}

	public function quote($string) {
		return $this->pdo->quote($string);
	}

	public function getBuilderName() {
		return $this->builder;
	}

	public function setBuilderName($builder) {
		$this->builder = $builder;
	}

	public function lastInsertId($column = null) {
		return $this->pdo->lastInsertId();
	}
}

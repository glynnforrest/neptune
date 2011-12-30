<?php

namespace neptune\database\drivers;

use \PDO;
use neptune\database\statements\GenericStatement;

/**
 * MysqlDriver
 * @author Glynn Forrest me@glynnforrest.com
 **/
class MysqlDriver implements DatabaseDriver {

	protected $builder = '\\neptune\\database\\builders\\GenericSQLBuilder';
	protected $pdo;

	public function __construct($host, $port, $user, $pass, $db) {
		$dsn = "mysql:host=$host;port=$port;
                dbname=$db";
		$options = array(
			 PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);
		$this->pdo = new PDO($dsn, $user, $pass, $options);
	}

	public function prepare($query, $options = array()) {
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
}

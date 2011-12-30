<?php
namespace neptune\database\statements;
use \PDOStatement;

/**
 * GenericStatement
 * TODO: Implement all the functions of PDOStatement!
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class GenericStatement implements DatabaseStatement{
	private $stmt;

	public function __construct(PDOStatement $statement) {
		$this->stmt = $statement;
	}

	public function execute($params = array()) {
		return $this->stmt->execute($params);
	}

	public function fetchObject() {
		return $this->stmt->fetchObject();
	}

	public function fetchAssoc() {
		return $this->stmt->fetch(\PDO::FETCH_ASSOC);
	}

	public function rowCount() {
		return $this->stmt->rowCount();
	}
	
}

?>

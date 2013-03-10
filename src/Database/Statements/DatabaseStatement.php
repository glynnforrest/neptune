<?php
namespace neptune\database\statements;
/**
 * DatabaseStatement
 * //TODO: Add all PDOStatement functions.
 * @author Glynn Forrest <me@glynnforrest.com>
 */
interface DatabaseStatement {

	public function execute($params = array());

	public function fetchObject();

	public function fetchAssoc();

	public function rowCount();

}

?>

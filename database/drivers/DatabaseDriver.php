<?php

/**
 * DatabaseDriver
 * @author Glynn Forrest <me@glynnforrest.com>
 * An interface that all database drivers using the
 * neptune database library must implement.
 */

namespace neptune\database\drivers;

interface DatabaseDriver {

	public function __construct($host, $port, $user, $pass, $db);

	/**
	 * @return DatabaseStatement
	 */
	public function prepare($query);

	public function quote($string);

	public function getBuilderName();

	public function setBuilderName($builder);

	public function lastInsertId($column = null);
}

?>

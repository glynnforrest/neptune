<?php

namespace Neptune\Database\Statements;

use Neptune\Database\Statements\DatabaseStatement;

use Crutches\ItemList as I;

/**
 * DebugStatement
 * @author Glynn Forrest me@glynnforrest.com
 **/
class DebugStatement implements DatabaseStatement {

	protected $query;

	public function __construct($query) {
		$this->query = $query;
	}

	public function execute($params = array()) {
		foreach(I::create($params)->surround('`')->getList() as $param) {
			$this->query = preg_replace('`\?`', $param, $this->query, 1);
		}
	}

	public function fetchObject(){

	}

	public function fetchAssoc(){
	}

	public function rowCount(){
	}

	public function getExecutedQuery() {
		return $this->query;
	}

}
?>

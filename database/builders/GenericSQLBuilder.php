<?php

namespace neptune\database\builders;

use neptune\database\SQLQuery;

/**
 * GenericSQLBuilder
 * @author Glynn Forrest me@glynnforrest.com
 **/
class GenericSQLBuilder extends SQLQuery {

	protected function formatSelectString() {
		$query = 'SELECT';
		if (!isset($this->query['FIELDS'])) {
			$query .= ' *';
		}
		foreach ($this->types['SELECT'] as $action) {
			if (isset($this->query[$action])) {
				switch ($action) {
				case 'FROM':
					$this->addFrom($query);
					break;
				case 'FIELDS':
					$this->addFields($query);
					break;
				case 'WHERE':
					$this->addWhere($query);
					break;
				case 'ORDER BY':
					$this->addOrderBy($query);
					break;
				case 'DISTINCT':
					$this->addDistinct($query);
					break;
				case 'OFFSET':
					if(isset($this->query['LIMIT'])) {
						$query .= ' OFFSET ' . $this->query['OFFSET'];
					}
					break;
				default:
					$query .= ' ' . $action . ' ' . $this->query[$action];
					break;
				}
			}
		}
		return $query;
	}

	protected function formatInsertString() {
		$query = 'INSERT';
		foreach ($this->types[$this->type] as $action) {
			if (isset($this->query[$action])) {
				switch ($action) {
				case 'FIELDS':
					$this->addInsertFields($query);
					break;
				default:
					$query .= ' ' . $action . ' ' . $this->query[$action];
				}
			}
		}
		return $query;
	}

	protected function formatUpdateString() {
		$query = 'UPDATE';
		foreach ($this->types[$this->type] as $action) {
			if (isset($this->query[$action])) {
				switch ($action) {
				case 'TABLES':
					$this->addTables($query);
					break;
				case 'FIELDS':
					$this->addUpdateFields($query);
					break;
				case 'WHERE':
					$this->addWhere($query);
					break;
				default:
					$query .= ' ' . $action . ' ' . $this->query[$action];
				}
			}
		}
		return $query;
	}

	protected function formatDeleteString() {
		$query = 'DELETE';
		foreach ($this->types['DELETE'] as $action) {
			if (isset($this->query[$action])) {
				switch ($action) {
				case 'FROM':
					$this->addFrom($query);
					break;
				case 'WHERE':
					$this->addWhere($query);
					break;
				default:
					$query .= ' ' . $action . ' ' . $this->query[$action];
					break;
				}
			}
		}
		return $query;
	}

	protected function addFrom(&$query) {
		$query .= ' FROM ';
		for ($i = 0; $i < count($this->query['FROM']) - 1; $i++) {
			$query .= $this->query['FROM'][$i] . ', ';
		}
		$query .= $this->query['FROM'][$i];
	}

	protected function addFields(&$query) {
		$query .= ' ';
		for ($i = 0; $i < count($this->query['FIELDS']) - 1; $i++) {
			$query .= '`'. $this->query['FIELDS'][$i] . '`, ';
		}
		$query .= '`' . $this->query['FIELDS'][$i] . '`';
	}

	protected function addWhere(&$query) {
		$query .= ' WHERE ';
		$query .= $this->query['WHERE'][0][0];
		if($this->query['WHERE'][0][1]) {
			$query .= ' ' . $this->query['WHERE'][0][1];
		}
		for ($i = 1; $i < count($this->query['WHERE']); $i++) {
			$query .= ' ' . $this->query['WHERE'][$i][2] . ' ';
			$query .= $this->query['WHERE'][$i][0];
			if($this->query['WHERE'][0][1]) {
				$query .= ' ' . $this->query['WHERE'][$i][1];
			}
		}
	}

	protected function addOrderBy(&$query) {
		$query .= ' ORDER BY ' . $this->query['ORDER BY'][0];
		$query .= ' ' . $this->query['ORDER BY'][1];
	}

	protected function addinsertfields(&$query) {
		$query .= ' (';
		for ($i = 0; $i < count($this->query['FIELDS']) - 1; $i++) {
			$query .= '`' . $this->query['FIELDS'][$i] . '`, ';
		}
		$query .= '`' . $this->query['FIELDS'][$i] . '`';
		$query .= ') VALUES (';
		for ($i = 0; $i < count($this->query['FIELDS']) - 1; $i++) {
			$query .= '?, ';
		}
		$query .= '?)';
	}

	protected function addUpdateFields(&$query) {
		$query .= ' SET ';
		for ($i = 0; $i < count($this->query['FIELDS']) - 1; $i++) {
			$query .= '`' . $this->query['FIELDS'][$i] . '` = ?, ';
		}
		$query .= '`' . $this->query['FIELDS'][$i] . '` = ?';
	}

	protected function addTables(&$query) {
		$query .= ' ';
		for ($i = 0; $i < count($this->query['TABLES']) - 1; $i++) {
			$query .= $this->query['TABLES'][$i] . ', ';
		}
		$query .= $this->query['TABLES'][$i];
	}

	protected function addDistinct(&$query) {
		$query .= ' DISTINCT';
	}
}

?>

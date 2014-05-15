<?php

namespace Neptune\Tests\Database;

use Neptune\Database\Query\MysqlQuery;

include __DIR__ . ('/../../../../bootstrap.php');

/**
 * MysqlQueryTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class MysqlQueryTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
        $this->driver = $this->getMock('Neptune\Database\Driver\DatabaseDriverInterface');
	}

	public function testSimpleSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test');
		$this->assertSame('SELECT * FROM `test`', $q->getSQL());
	}

	public function testOneFieldSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->fields('id');
		$this->assertSame('SELECT `id` FROM `test`', $q->getSQL());
	}

	public function testFieldsSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->fields(array('id','name','age'));
		$this->assertSame('SELECT `id`, `name`, `age` FROM `test`', $q->getSQL());
	}

	public function testFieldsSelectSplit() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->fields('one');
		$q->fields('two');
		$q->fields(3);
		$q->from('test');
		$this->assertSame('SELECT `one`, `two`, `3` FROM `test`', $q->getSQL());
	}

	public function testSelectDistinct() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->distinct()->from('table')->fields(array('id', 'name'));
		$this->assertSame('SELECT DISTINCT `id`, `name` FROM `table`', $q->getSQL());
	}

	public function testWhereNoValue() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('column1 = column2');
		$this->assertSame("SELECT * FROM `test` WHERE column1 = column2", $q->getSQL());
	}

	/* public function testWhereEmptyStringValue() { */
	/* 	$q = new MysqlQuery($this->driver, 'SELECT'); */
	/* 	$q->from('test')->where('column1 = ', ''); */
	/* 	$this->assertSame("SELECT * FROM `test`", $q->getSQL()); */
	/* } */

	public function testWhereZeroValue() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('column1 =', 0);
		$this->assertSame("SELECT * FROM `test` WHERE column1 = ?", $q->getSQL());
        $this->assertSame(array(0), $q->getParameters());
	}

	public function testWhereSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('id =', 5);
		$this->assertSame("SELECT * FROM `test` WHERE id = ?", $q->getSQL());
        $this->assertSame(array(5), $q->getParameters());
	}

	public function testWhereAndSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('id <', 10)->where('id >', 1);
		$this->assertSame("SELECT * FROM `test` WHERE id < ? AND id > ?", $q->getSQL());
		$q->where('name =', 'foo')->where('id <', 9);
		$this->assertSame("SELECT * FROM `test` WHERE id < ? AND id > ? AND name = ? AND id < ?", $q->getSQL());
        $this->assertSame(array(10, 1, 'foo', 9), $q->getParameters());
	}

	public function testAndWhereSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->andWhere('id <', 10)->andWhere('id >', 1);
		$this->assertSame("SELECT * FROM `test` WHERE id < ? AND id > ?", $q->getSQL());
		$q->andWhere('name =', 'foo')->andWhere('id <', 9);
		$this->assertSame("SELECT * FROM `test` WHERE id < ? AND id > ? AND name = ? AND id < ?", $q->getSQL());
        $this->assertSame(array(10, 1, 'foo', 9), $q->getParameters());
	}

	public function testWhereOrSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('id =', 13, 'or')->where('id =', 2, 'OR');
		$this->assertSame("SELECT * FROM `test` WHERE id = ? OR id = ?", $q->getSQL());
		$q->where('name =', 'foo', 'OR')->where('id =', 9, 'Or');
		$this->assertSame("SELECT * FROM `test` WHERE id = ? OR id = ? OR name = ? OR id = ?", $q->getSQL());
        $this->assertSame(array(13, 2, 'foo', 9), $q->getParameters());
	}

	public function testOrWhereSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->orWhere('id =', 13)->orWhere('id =', 2);
		$this->assertSame("SELECT * FROM `test` WHERE id = ? OR id = ?", $q->getSQL());
		$q->orWhere('name =', 'foo')->orWhere('id =', 9);
		$this->assertSame("SELECT * FROM `test` WHERE id = ? OR id = ? OR name = ? OR id = ?", $q->getSQL());
        $this->assertSame(array(13, 2, 'foo', 9), $q->getParameters());
	}

	public function testCombinedWhereSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('id =', 1)->orWhere('id =', 3)->orWhere('id =', 5)->andWhere('name =', 'foo');
		$this->assertSame("SELECT * FROM `test` WHERE id = ? OR id = ? OR id = ? AND name = ?", $q->getSQL());
        $this->assertSame(array(1, 3, 5, 'foo'), $q->getParameters());
	}

	public function testWhereInjection() {
		$input = '2; insert into test (name) values(\'hacked\')';
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('id =', $input);
		$this->assertSame("SELECT * FROM `test` WHERE id = ?", $q->getSQL());
        $this->assertSame(array('2; insert into test (name) values(\'hacked\')'), $q->getParameters());
	}

	public function testWhereZero() {
		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('test')
          ->limit(10)
          ->orderBy('date', 'DESC')
          ->where('boolean =', 0)
          ->orWhere('foo =', '0')
          ->andWhere('key =', '0');
		$expected = "SELECT * FROM `test` WHERE boolean = ? OR foo = ? AND key = ? ORDER BY date DESC LIMIT 10";
		$this->assertSame($expected, $q->getSQL());
        $this->assertSame(array(0, '0', '0'), $q->getParameters());
	}

	public function testWhereSelectParams() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('id = ? OR id = ?');
		$this->assertSame("SELECT * FROM `test` WHERE id = ? OR id = ?", $q->getSQL());
        $this->assertSame(array(null, null), $q->getParameters());
        $this->assertSame(array(0, 1), $q->getExpectedParameters());
	}

	public function testCombinedWhereSelectParams() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('id = ?')->orWhere('id =', '3');
		$this->assertSame("SELECT * FROM `test` WHERE id = ? OR id = ?", $q->getSQL());
        $this->assertSame(array(null, '3'), $q->getParameters());
        $this->assertSame(array(0), $q->getExpectedParameters());
	}

	public function testSelectMultipleTables() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from(array('test', 'test2'))->fields(array('test.id', 'test.name'));
		$q->fields('test2.id')->where('test.id = test2.id');
		$this->assertSame('SELECT `test.id`, `test.name`, `test2.id` FROM `test`, `test2` WHERE test.id = test2.id', $q->getSQL());
	}

	public function testWhereIn() {
		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('table');
		$q->whereIn('id', array(1,2,3));
		$this->assertSame('SELECT * FROM `table` WHERE id IN (1,2,3)',
			$q->getSQL());
	}

	public function testWhereInAnd() {
		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('table');
		$q->where('column =', '?')->andWhereIn('id', array('4,5,6'));
		$this->assertSame('SELECT * FROM `table` WHERE column = ? AND id IN (4,5,6)',
			$q->getSQL());
	}

	public function testWhereInOr() {
		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('table');
		$q->where('column =', '?')->orWhereIn('id', array('4,5,6'));
		$this->assertSame('SELECT * FROM `table` WHERE column = ? OR id IN (4,5,6)',
			$q->getSQL());
	}

	public function testSelectMultipleTablesSplit() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test');
		$q->from('test2');
		$q->fields('test.id');
		$q->fields('test.name');
		$q->fields('test2.id');
		$q->where('test.id = test2.id');
		$this->assertSame('SELECT `test.id`, `test.name`, `test2.id` FROM `test`, `test2` WHERE test.id = test2.id', $q->getSQL());
	}

	public function testSelectOrderBy() {
		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('test')->orderBy('RAND()');
		$this->assertSame('SELECT * FROM `test` ORDER BY RAND() ASC', $q->getSQL());

		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('test')->orderBy('id', 'desc');
		$this->assertSame('SELECT * FROM `test` ORDER BY id DESC', $q->getSQL());

		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('test')->orderBy('id', 'asc');
		$this->assertSame('SELECT * FROM `test` ORDER BY id ASC', $q->getSQL());

		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('test')->orderBy('id', 'foo');
		$this->assertSame('SELECT * FROM `test` ORDER BY id ASC', $q->getSQL());

		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('test')->orderBy('name')->orderBy('id', 'desc');
		$this->assertSame('SELECT * FROM `test` ORDER BY name ASC, id DESC', $q->getSQL());
	}

	public function testLimit() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->limit(3);
		$this->assertSame('SELECT * FROM `test` LIMIT 3', $q->getSQL());
	}

	public function testOffset() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->offset(2)->limit(3);
		$this->assertSame('SELECT * FROM `test` LIMIT 3 OFFSET 2', $q->getSQL());
	}

	public function testOffsetOnlyWhenLimitIsDefined() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->offset(2);
		$this->assertSame('SELECT * FROM `test`', $q->getSQL());
		$q->limit(3);
		$this->assertSame('SELECT * FROM `test` LIMIT 3 OFFSET 2', $q->getSQL());
	}

	public function testGetTables() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->from('test2');
		$this->assertSame(array('test','test2'), $q->getTables());
		$q = new MysqlQuery($this->driver, 'INSERT');
		$q->into('test');
		$this->assertSame(array('test'), $q->getTables());
		$q = new MysqlQuery($this->driver, 'UPDATE');
		$q->tables('test');
		$this->assertSame(array('test'), $q->getTables());
		$q = new MysqlQuery($this->driver, 'DELETE');
		$q->from('test');
		$this->assertSame(array('test'), $q->getTables());
	}

	public function testInsertSingle() {
		$q = new MysqlQuery($this->driver, 'INSERT');
		$q->into('users');
		$q->fields('one_field');
		$this->assertSame('INSERT INTO `users` (`one_field`) VALUES (?)', $q->getSQL());
	}

	public function testInsertMultiple() {
		$q = new MysqlQuery($this->driver, 'INSERT');
		$q->into('users');
		$q->fields(array('one', 'two', 'three'));
		$this->assertSame('INSERT INTO `users` (`one`, `two`, `three`) VALUES (?, ?, ?)', $q->getSQL());
	}

	public function testUpdate() {
		$q = new MysqlQuery($this->driver, 'UPDATE');
		$q->tables('test');
		$q->fields(array('field_1', 'field_2'));
		$this->assertSame('UPDATE `test` SET `field_1` = ?, `field_2` = ?', $q->getSQL());
	}

	public function testUpdateWhere() {
		$q = new MysqlQuery($this->driver, 'UPDATE');
		$q->tables('test')->where('id =', '?');
		$q->fields(array('field_1', 'field_2'));
		$this->assertSame('UPDATE `test` SET `field_1` = ?, `field_2` = ? WHERE id = ?', $q->getSQL());
	}

	public function testDelete() {
		$q = new MysqlQuery($this->driver, 'DELETE');
		$q->from('test');
		$this->assertSame('DELETE FROM `test`', $q->getSQL());
	}

	public function testDeleteWhere() {
		$q = new MysqlQuery($this->driver, 'DELETE');
		$q->from('123');
		$id = 12;
		$q->where('id =', $id);
		$this->assertSame('DELETE FROM `123` WHERE id = ?', $q->getSQL());
	}

	public function testJoin() {
		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('table')->join('table2');
		$this->assertSame('SELECT * FROM `table` JOIN table2', $q->getSQL());
	}

}

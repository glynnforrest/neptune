<?php

namespace Neptune\Tests\Database;

use Neptune\Core\Config;
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

	public function tearDown() {
	}

	public function testSimpleSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test');
		$this->assertEquals('SELECT * FROM `test`', $q->__toString());
	}

	public function testOneFieldSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->fields('id');
		$this->assertEquals('SELECT `id` FROM `test`', $q->__toString());
	}

	public function testFieldsSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->fields(array('id','name','age'));
		$this->assertEquals('SELECT `id`, `name`, `age` FROM `test`', $q->__toString());
	}

	public function testFieldsSelectSplit() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->fields('one');
		$q->fields('two');
		$q->fields(3);
		$q->from('test');
		$this->assertEquals('SELECT `one`, `two`, `3` FROM `test`', $q->__toString());
	}

	public function testSelectDistinct() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->distinct()->from('table')->fields(array('id', 'name'));
		$this->assertEquals('SELECT DISTINCT `id`, `name` FROM `table`',
			$q->__toString());
	}

	public function testWhereNoValue() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('column1 = column2');
		$this->assertEquals("SELECT * FROM `test` WHERE column1 = column2", $q->__toString());
	}

	public function testWhereNullValue() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('column1 = ', '');
		$this->assertEquals("SELECT * FROM `test`", $q->__toString());
	}

	public function testWhereZeroValue() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('column1 =', 0);
		$this->assertEquals("SELECT * FROM `test` WHERE column1 = '0'", $q->__toString());

		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('column2 =', '0');
		$this->assertEquals("SELECT * FROM `test` WHERE column2 = '0'", $q->__toString());
	}

	public function testWhereSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('id =', 5);
		$this->assertEquals("SELECT * FROM `test` WHERE id = '5'", $q->__toString());
	}

	public function testWhereAndSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('id <', 10)->where('id >', 1);
		$this->assertEquals("SELECT * FROM `test` WHERE id < '10' AND id > '1'", $q->__toString());
		$q->where('name =', 'Omar')->where('id <', 9);
		$this->assertEquals("SELECT * FROM `test` WHERE id < '10' AND id > '1' AND name = 'Omar' AND id < '9'", $q->__toString());
	}

	public function testAndWhereSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->andWhere('id <', 10)->andWhere('id >', 1);
		$this->assertEquals("SELECT * FROM `test` WHERE id < '10' AND id > '1'", $q->__toString());
		$q->andWhere('name =', 'Omar')->andWhere('id <', 9);
		$this->assertEquals("SELECT * FROM `test` WHERE id < '10' AND id > '1' AND name = 'Omar' AND id < '9'", $q->__toString());
	}

	public function testWhereOrSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('id =', 13, 'or')->where('id =', 2, 'OR');
		$this->assertEquals("SELECT * FROM `test` WHERE id = '13' OR id = '2'", $q->__toString());
		$q->where('name =', 'Omar', 'OR')->where('id =', 9, 'Or');
		$this->assertEquals("SELECT * FROM `test` WHERE id = '13' OR id = '2' OR name = 'Omar' OR id = '9'", $q->__toString());
	}

	public function testOrWhereSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->orWhere('id =', 13, 'or')->orWhere('id =', 2, 'OR');
		$this->assertEquals("SELECT * FROM `test` WHERE id = '13' OR id = '2'", $q->__toString());
		$q->orWhere('name =', 'Omar', 'OR')->orWhere('id =', 9, 'Or');
		$this->assertEquals("SELECT * FROM `test` WHERE id = '13' OR id = '2' OR name = 'Omar' OR id = '9'", $q->__toString());
	}

	public function testCombinedWhereSelect() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('id =', 1)->orWhere('id =', 3)->orWhere('id =', 5)->andWhere('name =', 'Omar');
		$this->assertEquals("SELECT * FROM `test` WHERE id = '1' OR id = '3' OR id = '5' AND name = 'Omar'", $q->__toString());
	}

	public function testWhereInjection() {
		$input = '2; insert into test (name) values(\'hacked\')';
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('id =', $input);
		$this->assertEquals("SELECT * FROM `test` WHERE id = '2; insert into test (name) values(\'hacked\')'", $q->__toString());
	}

	public function testWhereZero() {
		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('test')
          ->limit(10)
          ->orderBy('date', 'DESC')
          ->where('boolean =', '0')
          ->orWhere('foo =', '0')
          ->andWhere('key =', '0');
		$expected = "SELECT * FROM `test` WHERE boolean = '0' OR foo = '0' AND key = '0' ORDER BY date DESC LIMIT 10";
		$this->assertEquals($expected, $q->__toString());
	}

	public function testWhereSelectParams() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('id =', '?');
		$this->assertEquals("SELECT * FROM `test` WHERE id = ?", $q->__toString());
	}

	public function testCombinedWhereSelectParams() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->where('id =', '?')->orWhere('id =', '?')->orWhere('id =', '?')->andWhere('name =', '?');
		$this->assertEquals("SELECT * FROM `test` WHERE id = ? OR id = ? OR id = ? AND name = ?", $q->__toString());
	}

	public function testSelectMultipleTables() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from(array('test', 'test2'))->fields(array('test.id', 'test.name'));
		$q->fields('test2.id')->where('test.id = test2.id');
		$this->assertEquals('SELECT `test.id`, `test.name`, `test2.id` FROM `test`, `test2` WHERE test.id = test2.id', $q->__toString());
	}

	public function testWhereIn() {
		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('table');
		$q->whereIn('id', array(1,2,3));
		$this->assertEquals('SELECT * FROM `table` WHERE id IN (1,2,3)',
			$q->__toString());
	}

	public function testWhereInAnd() {
		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('table');
		$q->where('column =', '?')->andWhereIn('id', array('4,5,6'));
		$this->assertEquals('SELECT * FROM `table` WHERE column = ? AND id IN (4,5,6)',
			$q->__toString());
	}

	public function testWhereInOr() {
		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('table');
		$q->where('column =', '?')->orWhereIn('id', array('4,5,6'));
		$this->assertEquals('SELECT * FROM `table` WHERE column = ? OR id IN (4,5,6)',
			$q->__toString());
	}

	public function testSelectMultipleTablesSplit() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test');
		$q->from('test2');
		$q->fields('test.id');
		$q->fields('test.name');
		$q->fields('test2.id');
		$q->where('test.id = test2.id');
		$this->assertEquals('SELECT `test.id`, `test.name`, `test2.id` FROM `test`, `test2` WHERE test.id = test2.id', $q->__toString());
	}

	public function testSelectOrderBy() {
		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('test')->orderBy('RAND()');
		$this->assertEquals('SELECT * FROM `test` ORDER BY RAND() ASC', $q->__toString());

		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('test')->orderBy('id', 'desc');
		$this->assertEquals('SELECT * FROM `test` ORDER BY id DESC', $q->__toString());

		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('test')->orderBy('id', 'asc');
		$this->assertEquals('SELECT * FROM `test` ORDER BY id ASC', $q->__toString());

		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('test')->orderBy('id', 'foo');
		$this->assertEquals('SELECT * FROM `test` ORDER BY id ASC', $q->__toString());

		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('test')->orderBy('name')->orderBy('id', 'desc');
		$this->assertEquals('SELECT * FROM `test` ORDER BY name ASC, id DESC', $q->__toString());
	}

	public function testLimit() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->limit(3);
		$this->assertEquals('SELECT * FROM `test` LIMIT 3', $q->__toString());
	}

	public function testOffset() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->offset(2)->limit(3);
		$this->assertEquals('SELECT * FROM `test` LIMIT 3 OFFSET 2', $q->__toString());
	}

	public function testOffsetOnlyWhenLimitIsDefined() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->offset(2);
		$this->assertEquals('SELECT * FROM `test`', $q->__toString());
		$q->limit(3);
		$this->assertEquals('SELECT * FROM `test` LIMIT 3 OFFSET 2', $q->__toString());
	}

	public function testGetTables() {
		$q = new MysqlQuery($this->driver, 'SELECT');
		$q->from('test')->from('test2');
		$this->assertEquals(array('test','test2'), $q->getTables());
		$q = new MysqlQuery($this->driver, 'INSERT');
		$q->into('test');
		$this->assertEquals(array('test'), $q->getTables());
		$q = new MysqlQuery($this->driver, 'UPDATE');
		$q->tables('test');
		$this->assertEquals(array('test'), $q->getTables());
		$q = new MysqlQuery($this->driver, 'DELETE');
		$q->from('test');
		$this->assertEquals(array('test'), $q->getTables());
	}

	public function testInsertSingle() {
		$q = new MysqlQuery($this->driver, 'INSERT');
		$q->into('users');
		$q->fields('one_field');
		$this->assertEquals('INSERT INTO `users` (`one_field`) VALUES (?)', $q->__toString());
	}

	public function testInsertMultiple() {
		$q = new MysqlQuery($this->driver, 'INSERT');
		$q->into('users');
		$q->fields(array('one', 'two', 'three'));
		$this->assertEquals('INSERT INTO `users` (`one`, `two`, `three`) VALUES (?, ?, ?)', $q->__toString());
	}

	public function testUpdate() {
		$q = new MysqlQuery($this->driver, 'UPDATE');
		$q->tables('test');
		$q->fields(array('field_1', 'field_2'));
		$this->assertEquals('UPDATE `test` SET `field_1` = ?, `field_2` = ?', $q->__toString());
	}

	public function testUpdateWhere() {
		$q = new MysqlQuery($this->driver, 'UPDATE');
		$q->tables('test')->where('id =', '?');
		$q->fields(array('field_1', 'field_2'));
		$this->assertEquals('UPDATE `test` SET `field_1` = ?, `field_2` = ? WHERE id = ?', $q->__toString());
	}

	public function testDelete() {
		$q = new MysqlQuery($this->driver, 'DELETE');
		$q->from('test');
		$this->assertEquals('DELETE FROM `test`', $q->__toString());
	}

	public function testDeleteWhere() {
		$q = new MysqlQuery($this->driver, 'DELETE');
		$q->from('123');
		$id = 12;
		$q->where("id = '$id'");
		$this->assertEquals('DELETE FROM `123` WHERE id = \'12\'', $q->__toString());
	}

	public function testJoin() {
		$q = new MysqlQuery($this->driver, 'SELECT');
        $q->from('table')->join('table2');
		$this->assertEquals('SELECT * FROM `table` JOIN table2', $q->__toString());
	}

}

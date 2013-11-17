<?php

namespace Neptune\Tests\Database;

use Neptune\Core\Config;
use Neptune\Database\SQLQuery;
use Neptune\Database\Builders\GenericSQLBuilder;

include __DIR__ . ('/../../../../bootstrap.php');

/**
 * GenericSQLBuilderTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class GenericSQLBuilderTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$c = Config::create('unittest');
		$c->set('database', array(
			'unittest' => array(
				'driver' => 'debug',
				'database' => 'unittest'
			)
		));
	}

	public function tearDown() {
		Config::unload();
	}

	public function testConstruct() {
		$this->assertTrue(SQLQuery::select() instanceof GenericSQLBuilder);
		$this->assertTrue(SQLQuery::select('unittest') instanceof GenericSQLBuilder);
		$this->assertTrue(SQLQuery::insert() instanceof GenericSQLBuilder);
		$this->assertTrue(SQLQuery::insert('unittest') instanceof GenericSQLBuilder);
		$this->assertTrue(SQLQuery::update() instanceof GenericSQLBuilder);
		$this->assertTrue(SQLQuery::update('unittest') instanceof GenericSQLBuilder);
		$this->assertTrue(SQLQuery::delete() instanceof GenericSQLBuilder);
		$this->assertTrue(SQLQuery::delete('unittest') instanceof GenericSQLBuilder);
	}

	public function testConstructPrefix() {
		$c = Config::create('prefix');
		$c->set('database', array(
			'default' => array(
				'driver' => 'debug',
				'database' => 'default'
			),
			'second' => array(
				'driver' => 'debug',
				'database' => 'second'
			),
		));
		$this->assertTrue(SQLQuery::select('prefix#') instanceof GenericSQLBuilder);
		$this->assertTrue(SQLQuery::select('prefix#default') instanceof GenericSQLBuilder);
		$this->assertTrue(SQLQuery::insert('prefix#') instanceof GenericSQLBuilder);
		$this->assertTrue(SQLQuery::insert('prefix#default') instanceof GenericSQLBuilder);
		$this->assertTrue(SQLQuery::update('prefix#') instanceof GenericSQLBuilder);
		$this->assertTrue(SQLQuery::update('prefix#second') instanceof GenericSQLBuilder);
		$this->assertTrue(SQLQuery::delete('prefix#') instanceof GenericSQLBuilder);
		$this->assertTrue(SQLQuery::delete('prefix#second') instanceof GenericSQLBuilder);
	}


	public function testSimpleSelect() {
		$q = SQLQuery::select();
		$q->from('test');
		$this->assertEquals('SELECT * FROM test', $q->__toString());
	}

	public function testOneFieldSelect() {
		$q = SQLQuery::select();
		$q->from('test')->fields('id');
		$this->assertEquals('SELECT `id` FROM test', $q->__toString());
	}

	public function testFieldsSelect() {
		$q = SQLQuery::select();
		$q->from('test')->fields(array('id','name','age'));
		$this->assertEquals('SELECT `id`, `name`, `age` FROM test', $q->__toString());
	}

	public function testFieldsSelectSplit() {
		$q = SQLQuery::select();
		$q->fields('one');
		$q->fields('two');
		$q->fields(3);
		$q->from('test');
		$this->assertEquals('SELECT `one`, `two`, `3` FROM test', $q->__toString());
	}

	public function testSelectDistinct() {
		$q = SQLQuery::select()->distinct()->from('table')->fields(array('id',
			'name'));
		$this->assertEquals('SELECT DISTINCT `id`, `name` FROM table',
			$q->__toString());
	}

	public function testWhereNoValue() {
		$q = SQLQuery::select();
		$q->from('test')->where('column1 = column2');
		$this->assertEquals("SELECT * FROM test WHERE column1 = column2", $q->__toString());
	}

	public function testWhereNullValue() {
		$q = SQLQuery::select();
		$q->from('test')->where('column1 = ', '');
		$this->assertEquals("SELECT * FROM test", $q->__toString());
	}

	public function testWhereZeroValue() {
		$q = SQLQuery::select();
		$q->from('test')->where('column1 =', 0);
		$this->assertEquals("SELECT * FROM test WHERE column1 = '0'", $q->__toString());
		$q = SQLQuery::select();
		$q->from('test')->where('column2 =', '0');
		$this->assertEquals("SELECT * FROM test WHERE column2 = '0'", $q->__toString());
	}

	public function testWhereSelect() {
		$q = SQLQuery::select();
		$q->from('test')->where('id =', 5);
		$this->assertEquals("SELECT * FROM test WHERE id = '5'", $q->__toString());
	}

	public function testWhereAndSelect() {
		$q = SQLQuery::select();
		$q->from('test')->where('id <', 10)->where('id >', 1);
		$this->assertEquals("SELECT * FROM test WHERE id < '10' AND id > '1'", $q->__toString());
		$q->where('name =', 'Omar')->where('id <', 9);
		$this->assertEquals("SELECT * FROM test WHERE id < '10' AND id > '1' AND name = 'Omar' AND id < '9'", $q->__toString());
	}

	public function testAndWhereSelect() {
		$q = SQLQuery::select();
		$q->from('test')->andWhere('id <', 10)->andWhere('id >', 1);
		$this->assertEquals("SELECT * FROM test WHERE id < '10' AND id > '1'", $q->__toString());
		$q->andWhere('name =', 'Omar')->andWhere('id <', 9);
		$this->assertEquals("SELECT * FROM test WHERE id < '10' AND id > '1' AND name = 'Omar' AND id < '9'", $q->__toString());
	}

	public function testWhereOrSelect() {
		$q = SQLQuery::select();
		$q->from('test')->where('id =', 13, 'or')->where('id =', 2, 'OR');
		$this->assertEquals("SELECT * FROM test WHERE id = '13' OR id = '2'", $q->__toString());
		$q->where('name =', 'Omar', 'OR')->where('id =', 9, 'Or');
		$this->assertEquals("SELECT * FROM test WHERE id = '13' OR id = '2' OR name = 'Omar' OR id = '9'", $q->__toString());
	}

	public function testOrWhereSelect() {
		$q = SQLQuery::select();
		$q->from('test')->orWhere('id =', 13, 'or')->orWhere('id =', 2, 'OR');
		$this->assertEquals("SELECT * FROM test WHERE id = '13' OR id = '2'", $q->__toString());
		$q->orWhere('name =', 'Omar', 'OR')->orWhere('id =', 9, 'Or');
		$this->assertEquals("SELECT * FROM test WHERE id = '13' OR id = '2' OR name = 'Omar' OR id = '9'", $q->__toString());
	}

	public function testCombinedWhereSelect() {
		$q = SQLQuery::select();
		$q->from('test')->where('id =', 1)->orWhere('id =', 3)->orWhere('id =', 5)->andWhere('name =', 'Omar');
		$this->assertEquals("SELECT * FROM test WHERE id = '1' OR id = '3' OR id = '5' AND name = 'Omar'", $q->__toString());
	}

	public function testWhereInjection() {
		$input = '2; insert into test (name) values(\'hacked\')';
		$q = SQLQuery::select();
		$q->from('test')->where('id =', $input);
		$this->assertEquals("SELECT * FROM test WHERE id = '2; insert into test (name) values(\'hacked\')'", $q->__toString());
	}

	public function testWhereZero() {
		$q = SQLQuery::select()
			->from('test')
			->limit(10)
			->orderBy('date', 'DESC')
			->where('boolean =', '0')
			->orWhere('foo =', '0')
			->andWhere('key =', '0');
		$expected = "SELECT * FROM test WHERE boolean = '0' OR foo = '0' AND key = '0' ORDER BY date DESC LIMIT 10";
		$this->assertEquals($expected, $q->__toString());
	}

	public function testWhereSelectParams() {
		$q = SQLQuery::select();
		$q->from('test')->where('id =', '?');
		$this->assertEquals("SELECT * FROM test WHERE id = ?", $q->__toString());
	}

	public function testCombinedWhereSelectParams() {
		$q = SQLQuery::select();
		$q->from('test')->where('id =', '?')->orWhere('id =', '?')->orWhere('id =', '?')->andWhere('name =', '?');
		$this->assertEquals("SELECT * FROM test WHERE id = ? OR id = ? OR id = ? AND name = ?", $q->__toString());
	}

	public function testSelectMultipleTables() {
		$q = SQLQuery::select();
		$q->from(array('test', 'test2'))->fields(array('test.id', 'test.name'));
		$q->fields('test2.id')->where('test.id = test2.id');
		$this->assertEquals('SELECT `test.id`, `test.name`, `test2.id` FROM test, test2 WHERE test.id = test2.id', $q->__toString());
	}

	public function testWhereIn() {
		$q = SQLQuery::select()->from('table');
		$q->whereIn('id', array(1,2,3));
		$this->assertEquals('SELECT * FROM table WHERE id IN (1,2,3)',
			$q->__toString());
	}

	public function testWhereInAnd() {
		$q = SQLQuery::select()->from('table');
		$q->where('column =', '?')->andWhereIn('id', array('4,5,6'));
		$this->assertEquals('SELECT * FROM table WHERE column = ? AND id IN (4,5,6)',
			$q->__toString());
	}

	public function testWhereInOr() {
		$q = SQLQuery::select()->from('table');
		$q->where('column =', '?')->orWhereIn('id', array('4,5,6'));
		$this->assertEquals('SELECT * FROM table WHERE column = ? OR id IN (4,5,6)',
			$q->__toString());
	}

	public function testSelectMultipleTablesSplit() {
		$q = SQLQuery::select();
		$q->from('test');
		$q->from('test2');
		$q->fields('test.id');
		$q->fields('test.name');
		$q->fields('test2.id');
		$q->where('test.id = test2.id');
		$this->assertEquals('SELECT `test.id`, `test.name`, `test2.id` FROM test, test2 WHERE test.id = test2.id', $q->__toString());
	}

	public function testSelectOrderBy() {
		$q = SQLQuery::select()->from('test')->orderBy('RAND()');
		$this->assertEquals('SELECT * FROM test ORDER BY RAND() ASC', $q->__toString());
		$q = SQLQuery::select()->from('test')->orderBy('id', 'desc');
		$this->assertEquals('SELECT * FROM test ORDER BY id DESC', $q->__toString());
		$q = SQLQuery::select()->from('test')->orderBy('id', 'asc');
		$this->assertEquals('SELECT * FROM test ORDER BY id ASC', $q->__toString());
		$q = SQLQuery::select()->from('test')->orderBy('id', 'foo');
		$this->assertEquals('SELECT * FROM test ORDER BY id ASC', $q->__toString());
		$q = SQLQuery::select()->from('test')->orderBy('name')->orderBy('id', 'desc');
		$this->assertEquals('SELECT * FROM test ORDER BY name ASC, id DESC', $q->__toString());
	}

	public function testLimit() {
		$q = SQLQuery::select();
		$q->from('test')->limit(3);
		$this->assertEquals('SELECT * FROM test LIMIT 3', $q->__toString());
	}

	public function testOffset() {
		$q = SQLQuery::select();
		$q->from('test')->offset(2)->limit(3);
		$this->assertEquals('SELECT * FROM test LIMIT 3 OFFSET 2', $q->__toString());
	}

	public function testOffsetOnlyWhenLimitIsDefined() {
		$q = SQLQuery::select();
		$q->from('test')->offset(2);
		$this->assertEquals('SELECT * FROM test', $q->__toString());
		$q->limit(3);
		$this->assertEquals('SELECT * FROM test LIMIT 3 OFFSET 2', $q->__toString());
	}

	public function testGetTables() {
		$q = SQLQuery::select();
		$q->from('test')->from('test2');
		$this->assertEquals(array('test','test2'), $q->getTables());
		$q = SQLQuery::insert();
		$q->into('test');
		$this->assertEquals(array('test'), $q->getTables());
		$q = SQLQuery::update();
		$q->tables('test');
		$this->assertEquals(array('test'), $q->getTables());
		$q = SQLQuery::delete();
		$q->from('test');
		$this->assertEquals(array('test'), $q->getTables());
	}

	public function testInsertSingle() {
		$q = SQLQuery::insert();
		$q->into('users');
		$q->fields('one_field');
		$this->assertEquals('INSERT INTO users (`one_field`) VALUES (?)', $q->__toString());
	}

	public function testInsertMultiple() {
		$q = SQLQuery::insert();
		$q->into('users');
		$q->fields(array('one', 'two', 'three'));
		$this->assertEquals('INSERT INTO users (`one`, `two`, `three`) VALUES (?, ?, ?)', $q->__toString());
	}

	public function testUpdate() {
		$q = SQLQuery::update();
		$q->tables('test');
		$q->fields(array('field_1', 'field_2'));
		$this->assertEquals('UPDATE test SET `field_1` = ?, `field_2` = ?', $q->__toString());
	}

	public function testUpdateWhere() {
		$q = SQLQuery::update();
		$q->tables('test')->where('id =', '?');
		$q->fields(array('field_1', 'field_2'));
		$this->assertEquals('UPDATE test SET `field_1` = ?, `field_2` = ? WHERE id = ?', $q->__toString());
	}

	public function testDelete() {
		$q = SQLQuery::delete();
		$q->from('test');
		$this->assertEquals('DELETE FROM test', $q->__toString());
	}

	public function testDeleteWhere() {
		$q = SQLQuery::delete();
		$q->from('123');
		$id = 12;
		$q->where("id = '$id'");
		$this->assertEquals('DELETE FROM 123 WHERE id = \'12\'', $q->__toString());
	}

	public function testJoin() {
		$q = SQLQuery::select()->from('table')->join('table2');
		$this->assertEquals('SELECT * FROM table JOIN table2', $q->__toString());
	}

}

?>

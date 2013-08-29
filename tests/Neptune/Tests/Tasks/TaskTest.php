<?php

namespace Neptune\Tests\Tasks;

use Neptune\Core\Config;
use Neptune\Tests\Tasks\DummyTask;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * TaskTest
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TaskTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Config::create('neptune');
	}

	public function tearDown() {
		Config::unload();
	}

	public function testGetTaskMethods() {
		$t = new DummyTask();
		$expected = array('getTaskMethodsForTesting', 'help', 'run');
		$this->assertEquals($expected, $t->getTaskMethodsForTesting());
	}

	public function testHelp() {
		$t = new DummyTask();
		$expected = '';
		$this->assertEquals($expected, $t->help());
	}

}

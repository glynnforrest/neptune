<?php

namespace Neptune\Tests\Tasks;

use Neptune\Tasks\TaskRunner;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * TaskRunnerTest
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TaskRunnerTest extends \PHPUnit_Framework_TestCase {

	public function testConstruct() {
		$this->assertTrue(TaskRunner::getInstance() instanceof TaskRunner);
	}

	public function testParseSimple() {
		$t = TaskRunner::getInstance();
		$expected = array(
			'task' => 'TestTask',
			'method' => 'run',
			'args' => array(),
			'flags' => array()
		);
		$this->assertEquals($expected, $t->parse('test'));
		$expected['task'] = 'DoSomethingSpecialTask';
		$this->assertEquals($expected, $t->parse('do-something-special'));
	}

	public function testParseWithMethod() {
		$t = TaskRunner::getInstance();
		$expected = array(
			'task' => 'SetupTask',
			'method' => 'help',
			'args' => array(),
			'flags' => array()
		);
		$this->assertEquals($expected, $t->parse('setup:help'));
		$expected['method'] = 'helpMeGetStarted';
		$this->assertEquals($expected, $t->parse('setup:help-me-getStarted'));
	}

	public function testParseWithArguments() {
		$t = TaskRunner::getInstance();
		$expected = array(
			'task' => 'SetupTask',
			'method' => 'run',
			'args' => array('one', 'two'),
			'flags' => array()
		);
		$this->assertEquals($expected, $t->parse('setup one two'));
	}






}

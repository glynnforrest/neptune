<?php

namespace Neptune\Tests\Tasks;

use Neptune\Core\Config;
use Neptune\Tasks\TaskRunner;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * TaskRunnerTest
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TaskRunnerTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$c = Config::create('testing');
	}

	public function tearDown() {
		Config::unload();
	}

	public function testConstruct() {
		$this->assertTrue(TaskRunner::getInstance() instanceof TaskRunner);
	}

	public function testParseSimple() {
		$t = TaskRunner::getInstance();
		$expected = array(
			'task' => 'test',
			'method' => 'run',
			'args' => array(),
			'flags' => array()
		);
		$this->assertEquals($expected, $t->parse('test'));
		$expected['task'] = 'do-something-special';
		$this->assertEquals($expected, $t->parse('do-something-special'));
	}

	public function testParseWithMethod() {
		$t = TaskRunner::getInstance();
		$expected = array(
			'task' => 'setup',
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
			'task' => 'setup',
			'method' => 'run',
			'args' => array('one', 'two'),
			'flags' => array()
		);
		$this->assertEquals($expected, $t->parse('setup one two'));
	}

	public function testParseWithFlags() {
		$t = TaskRunner::getInstance();
		$expected = array(
			'task' => 'build',
			'method' => 'run',
			'args' => array(),
			'flags' => array('--verbose'),
		);
		$this->assertEquals($expected, $t->parse('build --verbose'));
	}

	public function testParseWithMethodAndFlags() {
		$t = TaskRunner::getInstance();
		$expected = array(
			'task' => 'build',
			'method' => 'setup',
			'args' => array(),
			'flags' => array('--verbose'),
		);
		$this->assertEquals($expected, $t->parse('build:setup --verbose'));
	}

	public function testParseWithMethodArgsAndFlags() {
		$t = TaskRunner::getInstance();
		$expected = array(
			'task' => 'create',
			'method' => 'model',
			'args' => array('foo', 'bar', 'baz'),
			'flags' => array('-v'),
		);
		$this->assertEquals($expected, $t->parse('create:model foo bar baz -v'));
	}

	public function testParseEmpty() {
		$t = TaskRunner::getInstance();
		$expected = array(
			'task' => 'setup',
			'method' => 'help',
			'args' => array(),
			'flags' => array(),
		);
		$this->assertEquals($expected, $t->parse(''));
		$this->assertEquals($expected, $t->parse('	 '));
	}


	public function testGetSetFlags() {
		$t = TaskRunner::getInstance();
		$t->setFlags(array('verbose'));
		$this->assertEquals(array('verbose'), $t->getFlags());
		$t->addFlags(array('verbose'));
		$this->assertEquals(array('verbose'), $t->getFlags());
		$t->setFlags(array('verbose', 'interactive'));
		$this->assertEquals(array('verbose', 'interactive'), $t->getFlags());
	}

	public function testSetFlagsStripsDashes() {
		$t = TaskRunner::getInstance();
		$t->setFlags(array('--verbose'));
		$this->assertEquals(array('verbose'), $t->getFlags());
	}

	public function testAddFlagsUsesAliases() {
		$t = TaskRunner::getInstance();
		$t->setFlags(array('v', 'i', 'V'));
		$this->assertEquals(array('verbose', 'interactive', 'version'), $t->getFlags());
	}

	public function testAddFlagsIgnoresCase() {
		$t = TaskRunner::getInstance();
		$t->setFlags(array('--vErbose', '-interactiVE', 'VERSION'));
		$this->assertEquals(array('verbose', 'interactive', 'version'), $t->getFlags());
	}

	public function testAddFlagsIgnoresDuplicates() {
		$t = TaskRunner::getInstance();
		$t->setFlags(array('--verbose', 'verbose'));
		$this->assertEquals(array('verbose'), $t->getFlags());
		$t->addFlags(array('-v'));
		$this->assertEquals(array('verbose'), $t->getFlags());
	}

	public function testGetTaskClass() {
		$t = TaskRunner::getInstance();
		$this->assertEquals('Neptune\\Tasks\\SetupTask', $t->getTaskClass('setup'));
		//pretend the Neptune\Tests namespace is our application and
		//check it loads the DummyTask
		$c = Config::load('testing');
		$c->set('namespace', 'Neptune\\Tests');
		$this->assertEquals('Neptune\\Tests\\Tasks\\DummyTask', $t->getTaskClass('dummy'));
		//it should fail if a task namespace isn't set
		$c->set('namespace', null);
		$this->setExpectedException('Neptune\\Exceptions\\ClassNotFoundException');
		$t->getTaskClass('dummy');
		//now do the same with defining other task namespaces
		$c->set('task.namespaces', array('Neptune\Tests\Tasks'));
		$this->assertEquals('Neptune\\Tests\\Tasks\\DummyTask', $t->getTaskClass('dummy'));
		//it should fail if a task namespace isn't set
		$c->set('task.namespaces', null);
		$this->setExpectedException('Neptune\\Exceptions\\ClassNotFoundException');
		$t->getTaskClass('dummy');
	}

}

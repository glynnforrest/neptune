<?php

namespace Neptune\Tests\Console;

use Neptune\Console\Console;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * ConsoleTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class ConsoleTest extends \PHPUnit_Framework_TestCase {

	public function testWrite() {
		$c = Console::getInstance();
		ob_start();
		$c->write('Hello world');
		$out = ob_get_clean();
		$this->assertEquals("Hello world\n", $out);
	}

	public function testWriteNoNewLine() {
		$c = Console::getInstance();
		ob_start();
		$c->write('Hello world', false);
		$out = ob_get_clean();
		$this->assertEquals("Hello world", $out);
	}

	public function testNoDefault() {
		$c = Console::getInstance();
		$c->setDefaultOption('Pick a colour', 'black');
		$this->assertEquals('Pick a colour', $c->addDefaultToPrompt('Pick a colour', null));
		$this->assertEquals('Pick a colour', $c->addDefaultToPrompt('Pick a colour', ""));
	}

	public function testNamedDefault() {
		$c = Console::getInstance();
		$c->setDefaultOption('Pick a colour', 'black');
		$this->assertEquals('Pick a colour (Default: white)', $c->addDefaultToPrompt('Pick a colour', 'white'));
	}

	public function testDefaultWorksWithZero() {
		$c = Console::getInstance();
		$this->assertEquals('Enter a number (Default: 0)', $c->addDefaultToPrompt('Enter a number', 0));
		$this->assertEquals('Enter a number (Default: 0)', $c->addDefaultToPrompt('Enter a number', '0'));
	}

	public function testLastDefault() {
		$c = Console::getInstance();
		$c->setDefaultOption('Pick a colour', 'black');
		$this->assertEquals('Pick a colour (Default: black)', $c->addDefaultToPrompt('Pick a colour', true));
	}

	public function testOptions() {
		$c = Console::getInstance();
		$expected = 'Pick a colour [0:black, 1:white] ';
		$actual = $c->options(array('black', 'white'), 'Pick a colour');
		$this->assertEquals($expected, $actual);
		$expected ='Pick a colour [0:black, 1:white, 2:red, 3:yellow, 4:green] ';
		$actual = $c->options(array('black', 'white', 'red', 'yellow', 'green'),
							  'Pick a colour');
		$this->assertEquals($expected, $actual);
	}

	public function testOptionsEmptyArray() {
		$c = Console::getInstance();
		$this->setExpectedException('\\Neptune\\Console\\ConsoleException');
		$c->options(array());
	}

}

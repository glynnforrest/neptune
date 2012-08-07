<?php

namespace neptune\console;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

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
	}

	public function testNamedDefault() {
		$c = Console::getInstance();
		$c->setDefaultOption('Pick a colour', 'black');
		$this->assertEquals('Pick a colour (Default: white)', $c->addDefaultToPrompt('Pick a colour', 'white'));
	}

	public function testLastDefault() {
		$c = Console::getInstance();
		$c->setDefaultOption('Pick a colour', 'black');
		$this->assertEquals('Pick a colour (Default: black)', $c->addDefaultToPrompt('Pick a colour', true));
	}

	public function testOptions() {
		$c = Console::getInstance();
		$this->assertEquals('Pick a colour [0:black, 1:white]: ',
							$c->options(array('black', 'white'), 'Pick a colour', null));
	}

}
?>
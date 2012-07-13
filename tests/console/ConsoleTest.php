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



}
?>
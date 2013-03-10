<?php

namespace Neptune\Core;

use Neptune\Core\Events;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * EventsTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class EventsTest extends \PHPUnit_Framework_TestCase {

	public function testSimpleEvent() {
		$e = Events::getInstance();
		$e->addHandler('test', function() {
			return 'hello world';
		});
		$this->assertEquals('hello world', $e->send('test'));
	}

	public function testEventOneArg() {
		$e = Events::getInstance();
		$e->addHandler('test', function($string) {
			return $string;
		});
		$this->assertEquals('foo', $e->send('test', 'foo'));
	}

	public function testEventTwoArgs() {
		$e = Events::getInstance();
		$e->addHandler('test', function($one, $two) {
			return "1 is $one, 2 is $two";
		});
		$this->assertEquals('1 is foo, 2 is bar', $e->send('test', array('foo', 'bar')));
	}


}
?>

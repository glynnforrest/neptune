<?php

namespace neptune\core;

use neptune\http\Request;

include dirname(__FILE__) . ('/../test_bootstrap.php');

/**
 * DispatcherTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class DispatcherTest extends \PHPUnit_Framework_TestCase {

	public function testGlobalsController() {
		$d = Dispatcher::getInstance();
		$d->globals(array(
			 'controller' => 'default'
		));
		$d->route('/func', array(
			 'function' => 'testFunction'
		));
		$d->route('.*', array(
			 'controller' => 'foo',
			 'function' => 'index'
		));
		$_SERVER['REQUEST_URI'] = '/';
		$this->assertTrue($this->compare('foo', 'index', array(), $d->getNextAction()));
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/func';
		$this->assertTrue($this->compare('default', 'testFunction', array(), $d->getNextAction()));
	}

	public function testNamedArgs() {
		$d = Dispatcher::getInstance();
		$d->route('/args/:id', array(
			 'controller' => 'foo',
			 'function' => 'test'
		));
		$d->route('/args/:id/:id2/:id3', array(
			 'controller' => 'bar',
			 'function' => 'go'
		));
		$_SERVER['REQUEST_URI'] = '/args/4';
		$this->assertTrue($this->compare('foo', 'test', array('id' => 4), $d->getNextAction()));
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/args/2/5/hello';
		$this->assertTrue($this->compare('bar', 'go', array('id' => 2,
						'id2' => 5,
						'id3' => 'hello')
							 , $d->getNextAction()));
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/fails';
		$this->assertFalse($d->getNextAction());
	}

	public function testDefaultArgs() {
		$d = Dispatcher::getInstance();
		$d->route('/hello(/:place)', array(
			 'controller' => 'foo',
			 'function' => 'hello',
			 'args' => array('place' => 'world')
		));
		$_SERVER['REQUEST_URI'] = '/hello';
		$this->assertTrue($this->compare('foo', 'hello', array('place' => 'world'), $d->getNextAction()));
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/hello/earth';
		$this->assertTrue($this->compare('foo', 'hello', array('place' => 'earth'), $d->getNextAction()));
	}

	public function testAutoRoute() {
		$d = Dispatcher::getInstance();
		$d->route('/:controller(/:function)(/:args)', array(
			 'function' => 'index',
			 'args' => array(1)
		));
		$_SERVER['REQUEST_URI'] = '/foo';
		$this->assertTrue($this->compare('foo', 'index', array(1), $d->getNextAction()));
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/foo/test';
		$this->assertTrue($this->compare('foo', 'test', array(1), $d->getNextAction()));
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/foo/test/4';
		$this->assertTrue($this->compare('foo', 'test', array(4), $d->getNextAction()));
	}

	public function testAutoArgsArray() {
		$d = Dispatcher::getInstance();
		$d->globals(array(
			 'argsFormat' => Dispatcher::ARGS_EXPLODE
		));
		$d->route('/hello(/:args)', array(
			 'controller' => 'testController',
			 'function' => 'foo'
		));
		$_SERVER['REQUEST_URI'] = '/hello';
		$this->assertTrue($this->compare('testController', 'foo', array(), $d->getNextAction()));
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/hello/test';
		$this->assertTrue($this->compare('testController', 'foo', array(0 => 'test'), $d->getNextAction()));
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/hello/test/hello';
		$this->assertTrue($this->compare('testController', 'foo', array(0 => 'test', 1 => 'hello'), $d->getNextAction()));
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/hello/test/hello/h3llo*&';
		$this->assertTrue($this->compare('testController', 'foo', array(0 => 'test', 1 => 'hello', 2 => 'h3llo*&'), $d->getNextAction()));
	}

	public function testAutoArgsSingle() {
		$d = Dispatcher::getInstance();
		$d->globals(array(
			 'argsFormat' => Dispatcher::ARGS_SINGLE
		));
		$d->route('f00(/:args)', array(
			 'controller' => 'test',
			 'function' => 'hmm'
		));
		$_SERVER['REQUEST_URI'] = 'f00/argument';
		$this->assertTrue($this->compare('test', 'hmm', array(0 => 'argument'), $d->getNextAction()));
		$this->reset();
		$_SERVER['REQUEST_URI'] = 'f00/a/r/gu/mentwith_some_therstuff$4';
		$this->assertTrue($this->compare('test', 'hmm', array(0 => 'a/r/gu/mentwith_some_therstuff$4'), $d->getNextAction()));
	}

	public function testValidatedArgs() {
		$d = Dispatcher::getInstance();
		$d->route('/email/:email', array(
			 'rules' => array(
				  'email' => 'email'
			 ),
			 'controller' => 'emailController',
			 'function' => 'sendMail'
		));
		$_SERVER['REQUEST_URI'] = '/email/me@glynnforrest.com.html';
		$this->assertTrue($this->compare('emailController', 'sendMail', array('email' => 'me@glynnforrest.com'), $d->getNextAction()));
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/email/me@glynnforrestcom';
		$this->assertFalse($d->getNextAction());
		$this->reset();
		$d->route('/int/:int', array(
			 'rules' => array(
				  'int' => 'int'
			 ),
			 'controller' => 'intController',
			 'function' => 'int'
		));
		$_SERVER['REQUEST_URI'] = '/int/4';
		$this->assertTrue($this->compare('intController', 'int', array('int' => '4'), $d->getNextAction()));
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/int/four';
		$this->assertFalse($d->getNextAction());
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/int/4.3/.html';
		$this->assertFalse($d->getNextAction());
	}

	public function testTransforms() {
		$d = Dispatcher::getInstance();
		$d->globals(array(
			 'transforms' => array(
				  'controller' => function($string) {
					  return ucfirst($string) . 'Controller';
				  }
			 )
		));
		$d->route('/:controller', array(
			 'function' => 'index'
		));
		$_SERVER['REQUEST_URI'] = '/foo';
		$this->assertTrue($this->compare('FooController', 'index', array(), $d->getNextAction()));
		$d->route('/:controller/:function', array(
			 'transforms' => array(
				  'function' => function ($func) {
					  return strtoupper($func);
				  }
			 )
		));
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/bar/hello';
		$this->assertTrue($this->compare('BarController', 'HELLO', array(), $d->getNextAction()));
		$d->route('/:controller/:function/:name/:age', array(
			 'transforms' => array(
				  'function' => function ($func) {
					  return strtoupper($func);
				  },
				  'name' => function($name) {
					  return ucfirst($name);
				  },
				  'age' => function($age) {
					  return ($age + 2) / 2;
				  }
			 )
		));
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/person/name_age/glynn/20';
		$this->assertTrue($this->compare('PersonController', 'NAME_AGE', array(
						'name' => 'Glynn',
						'age' => 11
							 ), $d->getNextAction()));
	}

	public function testOneFormat() {
		$d = Dispatcher::getInstance();
		$d->globals(array());
		$d->route('/foo', array(
			 'controller' => 'test',
			 'function' => 'foo',
			 'format' => 'json'
		));
		$d->route('/:controller(/:function)(/:args)', array(
			 'function' => 'index',
			 'args' => array(0 => 1),
			 'format' => 'xml'
		));
		$_SERVER['REQUEST_URI'] = '/foo.json';
		$this->assertTrue($this->compare('test', 'foo', array(), $d->getNextAction()));
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/sweet.xml';
		$this->assertTrue($this->compare('sweet', 'index', array(0 => 1), $d->getNextAction()));
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/sweet.ml';
		$this->assertFalse($d->getNextAction());
		$this->reset();
		$_SERVER['REQUEST_URI'] = '/sweet';
		$this->assertFalse($d->getNextAction());
	}

	public function testGetRouteUrlZeroIndex() {
		$d = Dispatcher::getInstance();
		$d->route('/foo', array(
			 'controller' => 'test',
			 'function' => 'foo',
			 'name' => 'my_route'
		));
		$this->assertEquals('/foo', $d->getRouteUrl('my_route'));
	}

	public function testGetRouteUrl() {
		$d = Dispatcher::getInstance();
		$d->route('/var', array());
		$d->route('/foo/:variable(/:second)', array(
			 'controller' => 'test',
			 'function' => 'foo',
			 'name' => 'other_route'
		));
		$this->assertEquals('/foo/:variable(/:second)',
			$d->getRouteUrl('other_route'));
	}

	public function testGetRouteUrlNull() {
		$d = Dispatcher::getInstance();
		$d->route('/var', array());
		$this->assertNull($d->getRouteUrl('route'));
	}
}

?>

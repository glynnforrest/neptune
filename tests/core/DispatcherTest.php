<?php

namespace neptune\core;

include dirname(__FILE__) . ('/../test_bootstrap.php');

/**
 * DispatcherTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class DispatcherTest extends \PHPUnit_Framework_TestCase {

	protected function compare($controller, $function, $args, $array) {
		$expected = array('controller' => $controller,
			 'function' => $function,
			 'args' => $args);
		return $expected == $array;
	}

	public function testHomeRoute() {
		$d = Dispatcher::getInstance()->reset()->clear();
		$d->route('/', array(
			 'controller' => 'test',
			 'function' => 'foo'
		));
		$_SERVER['REQUEST_URI'] = '/';
		$this->assertTrue($this->compare('test', 'foo', array(), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '';
		$this->assertFalse($d->getNextAction());
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/hi';
		$this->assertFalse($d->getNextAction());
		$d->reset();
		$_SERVER['REQUEST_URI'] = ' / ';
		$this->assertFalse($d->getNextAction());
	}

	public function testCatchAll() {
		$d = Dispatcher::getInstance()->reset()->clear();
		$d->catchAll('test');
		$this->assertTrue($this->compare('test', 'index', array(), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '';
		$this->assertTrue($this->compare('test', 'index', array(), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '5.*7';
		$this->assertTrue($this->compare('test', 'index', array(), $d->getNextAction()));
	}

	public function testExplicitMatch() {
		$d = Dispatcher::getInstance()->reset()->clear();
		$d->route('/hello', array(
			 'controller' => 'hello',
			 'function' => 'world'
		));
		$_SERVER['REQUEST_URI'] = '/hello';
		$this->assertTrue($this->compare('hello', 'world', array(), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/';
		$this->assertFalse($d->getNextAction());
		$d->reset();
		$_SERVER['REQUEST_URI'] = '';
		$this->assertFalse($d->getNextAction());
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/hel';
		$this->assertFalse($d->getNextAction());
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/helloagain';
		$this->assertFalse($d->getNextAction());
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/h/e/l/l/o';
		$this->assertFalse($d->getNextAction());
	}

	public function testControllerMatch() {
		$d = Dispatcher::getInstance()->reset()->clear();
		$d->route('/test/:controller', array(
			 'function' => 'index'
		));
		$d->route('/:controller', array(
			 'function' => 'index'
		));
		$_SERVER['REQUEST_URI'] = '/test/test';
		$this->assertTrue($this->compare('test', 'index', array(), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/foo';
		$this->assertTrue($this->compare('foo', 'index', array(), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/testing/test';
		$this->assertFalse($d->getNextAction());
		$d->reset();
		$_SERVER['REQUEST_URI'] = 'foo';
		$this->assertFalse($d->getNextAction());
	}

	public function testGlobalsController() {
		$d = Dispatcher::getInstance()->reset()->clear();
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
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/func';
		$this->assertTrue($this->compare('default', 'testFunction', array(), $d->getNextAction()));
	}

	public function testArgsExplicitMatch() {
		$d = Dispatcher::getInstance()->reset()->clear();
		$d->route('/explicit', array(
			 'controller' => 'foo',
			 'function' => 'test',
			 'args' => array('id' => 1)
		));
		$_SERVER['REQUEST_URI'] = '/explicit';
		$this->assertTrue($this->compare('foo', 'test', array('id' => 1), $d->getNextAction()));
	}

	public function testNamedArgs() {
		$d = Dispatcher::getInstance()->reset()->clear();
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
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/args/2/5/hello';
		$this->assertTrue($this->compare('bar', 'go', array('id' => 2,
						'id2' => 5,
						'id3' => 'hello')
							 , $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/fails';
		$this->assertFalse($d->getNextAction());
	}

	public function testDefaultArgs() {
		$d = Dispatcher::getInstance()->reset()->clear();
		$d->route('/hello(/:place)', array(
			 'controller' => 'foo',
			 'function' => 'hello',
			 'args' => array('place' => 'world')
		));
		$_SERVER['REQUEST_URI'] = '/hello';
		$this->assertTrue($this->compare('foo', 'hello', array('place' => 'world'), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/hello/earth';
		$this->assertTrue($this->compare('foo', 'hello', array('place' => 'earth'), $d->getNextAction()));
	}

	public function testAutoRoute() {
		$d = Dispatcher::getInstance()->reset()->clear();
		$d->route('/:controller(/:function)(/:args)', array(
			 'function' => 'index',
			 'args' => array(1)
		));
		$_SERVER['REQUEST_URI'] = '/foo';
		$this->assertTrue($this->compare('foo', 'index', array(1), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/foo/test';
		$this->assertTrue($this->compare('foo', 'test', array(1), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/foo/test/4';
		$this->assertTrue($this->compare('foo', 'test', array(4), $d->getNextAction()));
	}

	public function testAutoArgsArray() {
		$d = Dispatcher::getInstance()->reset()->clear();
		$d->globals(array(
			 'argsFormat' => Dispatcher::ARGS_EXPLODE
		));
		$d->route('/hello(/:args)', array(
			 'controller' => 'testController',
			 'function' => 'foo'
		));
		$_SERVER['REQUEST_URI'] = '/hello';
		$this->assertTrue($this->compare('testController', 'foo', array(), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/hello/test';
		$this->assertTrue($this->compare('testController', 'foo', array(0 => 'test'), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/hello/test/hello';
		$this->assertTrue($this->compare('testController', 'foo', array(0 => 'test', 1 => 'hello'), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/hello/test/hello/h3llo*&';
		$this->assertTrue($this->compare('testController', 'foo', array(0 => 'test', 1 => 'hello', 2 => 'h3llo*&'), $d->getNextAction()));
	}

	public function testAutoArgsSingle() {
		$d = Dispatcher::getInstance()->reset()->clear();
		$d->globals(array(
			 'argsFormat' => Dispatcher::ARGS_SINGLE
		));
		$d->route('f00(/:args)', array(
			 'controller' => 'test',
			 'function' => 'hmm'
		));
		$_SERVER['REQUEST_URI'] = 'f00/argument';
		$this->assertTrue($this->compare('test', 'hmm', array(0 => 'argument'), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = 'f00/a/r/gu/mentwith_some_therstuff$4';
		$this->assertTrue($this->compare('test', 'hmm', array(0 => 'a/r/gu/mentwith_some_therstuff$4'), $d->getNextAction()));
	}

	public function testValidatedArgs() {
		$d = Dispatcher::getInstance()->reset()->clear();
		$d->route('/email/:email', array(
			 'rules' => array(
				  'email' => 'email'
			 ),
			 'controller' => 'emailController',
			 'function' => 'sendMail'
		));
		$_SERVER['REQUEST_URI'] = '/email/me@glynnforrest.com.html';
		$this->assertTrue($this->compare('emailController', 'sendMail', array('email' => 'me@glynnforrest.com'), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/email/me@glynnforrestcom';
		$this->assertFalse($d->getNextAction());
		$d->route('/int/:int', array(
			 'rules' => array(
				  'int' => 'int'
			 ),
			 'controller' => 'intController',
			 'function' => 'int'
		));
		$_SERVER['REQUEST_URI'] = '/int/4';
		$this->assertTrue($this->compare('intController', 'int', array('int' => '4'), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/int/four';
		$this->assertFalse($d->getNextAction());
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/int/4.3/.html';
		$this->assertFalse($d->getNextAction());
	}

	public function testTransforms() {
		$d = Dispatcher::getInstance()->reset()->clear();
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
		$d->reset();
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
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/person/name_age/glynn/20';
		$this->assertTrue($this->compare('PersonController', 'NAME_AGE', array(
						'name' => 'Glynn',
						'age' => 11
							 ), $d->getNextAction()));
	}

	public function testOneFormat() {
		$d = Dispatcher::getInstance()->reset()->clear();
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
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/sweet.xml';
		$this->assertTrue($this->compare('sweet', 'index', array(0 => 1), $d->getNextAction()));
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/sweet.ml';
		$this->assertFalse($d->getNextAction());
		$d->reset();
		$_SERVER['REQUEST_URI'] = '/sweet';
		$this->assertFalse($d->getNextAction());
	}

	public function testGetRouteUrlZeroIndex() {
		$d = Dispatcher::getInstance()->reset()->clear();
		$d->route('/foo', array(
			 'controller' => 'test',
			 'function' => 'foo',
			 'name' => 'my_route'
		));
		$this->assertEquals('/foo', $d->getRouteUrl('my_route'));
	}

	public function testGetRouteUrl() {
		$d = Dispatcher::getInstance()->reset()->clear();
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
		$d = Dispatcher::getInstance()->reset()->clear();
		$d->route('/var', array());
		$this->assertNull($d->getRouteUrl('route'));
	}
}

?>

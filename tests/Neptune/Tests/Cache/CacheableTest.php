<?php

namespace Neptune\Tests\Cache;

use Neptune\Cache\Cacheable;
use Neptune\Tests\Cache\FooCacheable;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * CacheableTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CacheableTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$this->obj = new FooCacheable();
	}

	public function tearDown() {
	}

	public function testFooIsCacheable() {
		$this->assertInstanceOf('\Neptune\Cache\Cacheable', $this->obj);
	}

	public function testSetAndGetCache() {
		$driver = $this->getMock('\Doctrine\Common\Cache\Cache');
		$this->obj->setCache($driver);
		$this->assertEquals($driver, $this->obj->getCache());
	}

	public function testCallMethodWithoutCache() {
		$this->assertSame('Foo', $this->obj->fooCached());
	}

	public function testCallMethod() {
		$driver = $this->getMock('\Doctrine\Common\Cache\Cache');
		$key = md5('Neptune\Tests\Cache\FooCacheable:foo');
		$driver->expects($this->once())
			   ->method('fetch')
			   ->with($key);
		$driver->expects($this->once())
			   ->method('save')
			   ->with($key, 'Foo');
		$this->obj->setCache($driver);
		$this->assertSame('Foo', $this->obj->fooCached());
	}

	public function argsProvider() {
		return array(
			array(array()),
			array(array('foo')),
			array(array(1, 'bar')),
		);
	}

	protected function createKey($args) {
		$key = 'Neptune\Tests\Cache\FooCacheable:foo';
		foreach ($args as $arg) {
			$key .= ':' . serialize($arg);
		}
		return md5($key);
	}

	/**
	 * @dataProvider argsProvider()
	 */
	public function testCallMethodWithArguments($arg1 = null, $arg2 = null, $arg3 = null) {
		$key = $this->createKey(array($arg1, $arg2, $arg3));
		$driver = $this->getMock('\Doctrine\Common\Cache\Cache');
		$driver->expects($this->once())
			   ->method('fetch')
			   ->with($key);
		$driver->expects($this->once())
			   ->method('save')
			   ->with($key, 'Foo');
		$this->obj->setCache($driver);
		$this->assertSame('Foo', $this->obj->fooCached($arg1, $arg2, $arg3));
	}

	public function testCallUnknownMethod() {
		$this->setExpectedException('\Neptune\Exceptions\MethodNotFoundException');
		$this->obj->unknown();
	}

	public function testCallUnknownMethodEndsWithCached() {
		$this->setExpectedException('\Neptune\Exceptions\MethodNotFoundException');
		$this->obj->unknownCached();
	}

	public function testMethodIsNotCalledWhenCacheIsHit() {
		$driver = $this->getMock('\Doctrine\Common\Cache\Cache');
		$driver->expects($this->once())
			   ->method('fetch')
			   ->with(md5('Neptune\Tests\Cache\FooCacheable:bar'))
			   ->will($this->returnValue('Output of bar()'));
		$driver->expects($this->never())
			   ->method('save');
		//mock a class that is called inside bar(). If it isn't
		//called, then the bar() has not run
		$bar = $this->getMock('stdClass');
		$bar->expects($this->never())
			->method('baz');
		$this->obj->setBar($bar);
		$this->obj->setCache($driver);
		$this->assertEquals('Output of bar()', $this->obj->barCached());
	}

	public function testMethodIsNotCalledWhenCacheReturnsFalse() {
		$driver = $this->getMock('\Doctrine\Common\Cache\Cache');
		$driver->expects($this->once())
			   ->method('fetch')
			   ->with(md5('Neptune\Tests\Cache\FooCacheable:bar'))
			   ->will($this->returnValue(false));
		$driver->expects($this->never())
			   ->method('save');
		$this->obj->setCache($driver);
		//mock a class that is called inside bar(). If it isn't
		//called, then the bar() has not run
		$bar = $this->getMock('stdClass');
		$bar->expects($this->never())
			->method('baz');
		$this->obj->setBar($bar);
		$this->assertEquals(false, $this->obj->barCached());
	}

	public function testDifferentArgsDifferentResults() {
		$driver = $this->getMock('\Doctrine\Common\Cache\Cache');
		$key1 = $this->createKey(array(1));
		$key2 = $this->createKey(array(2));
		$map = array(
			array($key1, 'Foo1'),
			array($key2, 'Foo2')
		);
		$driver->expects($this->exactly(2))
			   ->method('fetch')
			   ->will($this->returnValueMap($map));
		$driver->expects($this->never())
			   ->method('save');
		$this->obj->setCache($driver);
		$this->assertSame('Foo1', $this->obj->fooCached(1));
		$this->assertSame('Foo2', $this->obj->fooCached(2));
	}

}

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

	public function testSetAndGetCacheDriver() {
		$driver = $this->getMock('\Neptune\Cache\Driver\CacheDriverInterface');
		$this->obj->setCacheDriver($driver);
		$this->assertEquals($driver, $this->obj->getCacheDriver());
	}

	public function testCallMethodWithoutCache() {
		$this->assertSame('Foo', $this->obj->fooCached());
	}

	public function testCallMethod() {
		$driver = $this->getMock('\Neptune\Cache\Driver\CacheDriverInterface');
		$key = md5('Neptune\Tests\Cache\FooCacheable:foo');
		$driver->expects($this->once())
			   ->method('get')
			   ->with($key);
		$driver->expects($this->once())
			   ->method('set')
			   ->with($key, 'Foo');
		$this->obj->setCacheDriver($driver);
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
		$driver = $this->getMock('\Neptune\Cache\Driver\CacheDriverInterface');
		$driver->expects($this->once())
			   ->method('get')
			   ->with($key);
		$driver->expects($this->once())
			   ->method('set')
			   ->with($key, 'Foo');
		$this->obj->setCacheDriver($driver);
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
		$driver = $this->getMock('\Neptune\Cache\Driver\CacheDriverInterface');
		$driver->expects($this->once())
			   ->method('get')
			   ->with(md5('Neptune\Tests\Cache\FooCacheable:bar'))
			   ->will($this->returnValue('Output of bar()'));
		$driver->expects($this->never())
			   ->method('set');
		//mock a class that is called inside bar(). If it isn't
		//called, then the bar() has not run
		$bar = $this->getMock('stdClass');
		$bar->expects($this->never())
			->method('baz');
		$this->obj->setBar($bar);
		$this->obj->setCacheDriver($driver);
		$this->assertEquals('Output of bar()', $this->obj->barCached());
	}

	public function testMethodIsNotCalledWhenCacheReturnsFalse() {
		$driver = $this->getMock('\Neptune\Cache\Driver\CacheDriverInterface');
		$driver->expects($this->once())
			   ->method('get')
			   ->with(md5('Neptune\Tests\Cache\FooCacheable:bar'))
			   ->will($this->returnValue(false));
		$driver->expects($this->never())
			   ->method('set');
		$this->obj->setCacheDriver($driver);
		//mock a class that is called inside bar(). If it isn't
		//called, then the bar() has not run
		$bar = $this->getMock('stdClass');
		$bar->expects($this->never())
			->method('baz');
		$this->obj->setBar($bar);
		$this->assertEquals(false, $this->obj->barCached());
	}

	public function testDifferentArgsDifferentResults() {
		$driver = $this->getMock('\Neptune\Cache\Driver\CacheDriverInterface');
		$key1 = $this->createKey(array(1));
		$key2 = $this->createKey(array(2));
		$map = array(
			array($key1, true, 'Foo1'),
			array($key2, true, 'Foo2')
		);
		$driver->expects($this->exactly(2))
			   ->method('get')
			   ->will($this->returnValueMap($map));
		$driver->expects($this->never())
			   ->method('set');
		$this->obj->setCacheDriver($driver);
		$this->assertSame('Foo1', $this->obj->fooCached(1));
		$this->assertSame('Foo2', $this->obj->fooCached(2));
	}

}

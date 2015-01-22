<?php

namespace Neptune\Tests\Cache;

/**
 * CacheableTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CacheableTest extends \PHPUnit_Framework_TestCase
{
    protected $obj;
    protected $cache;

    public function setUp()
    {
        $this->obj = new FooCacheable();
        $this->cache = $this->getMock('Doctrine\Common\Cache\Cache');
    }

    public function testSetAndGetCache()
    {
        $this->obj->setCache($this->cache);
        $this->assertSame($this->cache, $this->obj->getCache());
    }

    public function testCallMethodWithoutCache()
    {
        $this->assertSame('Foo', $this->obj->fooCached());
    }

    public function testCallMethod()
    {
        $key = md5('Neptune\Tests\Cache\FooCacheable:foo');
        $this->cache->expects($this->once())
                    ->method('fetch')
                    ->with($key);
        $this->cache->expects($this->once())
                    ->method('save')
                    ->with($key, 'Foo');
        $this->obj->setCache($this->cache);
        $this->assertSame('Foo', $this->obj->fooCached());
    }

    public function argsProvider()
    {
        return [
            [[]],
            [['foo']],
            [[1, 'foo']],
        ];
    }

    protected function createKey($args)
    {
        $key = 'Neptune\Tests\Cache\FooCacheable:foo';
        foreach ($args as $arg) {
            $key .= ':'.serialize($arg);
        }

        return md5($key);
    }

    /**
     * @dataProvider argsProvider()
     */
    public function testCallMethodWithArguments($arg1 = null, $arg2 = null, $arg3 = null)
    {
        $key = $this->createKey([$arg1, $arg2, $arg3]);
        $this->cache->expects($this->once())
                    ->method('fetch')
                    ->with($key);
        $this->cache->expects($this->once())
                    ->method('save')
                    ->with($key, 'Foo');
        $this->obj->setCache($this->cache);
        $this->assertSame('Foo', $this->obj->fooCached($arg1, $arg2, $arg3));
    }

    public function testCallUnknownMethod()
    {
        $this->setExpectedException('\Neptune\Exceptions\MethodNotFoundException');
        $this->obj->unknown();
    }

    public function testCallUnknownMethodEndsWithCached()
    {
        $this->setExpectedException('\Neptune\Exceptions\MethodNotFoundException');
        $this->obj->unknownCached();
    }

    public function testMethodIsNotCalledWhenCacheIsHit()
    {
        $this->cache->expects($this->once())
                    ->method('fetch')
                    ->with(md5('Neptune\Tests\Cache\FooCacheable:configUsingMethod'))
                    ->will($this->returnValue('Config value'));
        $this->cache->expects($this->never())
                    ->method('save');
        //mock a class that is called inside config(). If it isn't
        //called, then the config() has not run
        $config = $this->getMock('Neptune\Config\Config');
        $config->expects($this->never())
               ->method('get');
        $this->obj->setConfig($config);
        $this->obj->setCache($this->cache);
        $this->assertSame('Config value', $this->obj->configUsingMethodCached());
    }

    public function testMethodIsNotCalledWhenCacheReturnsFalse()
    {
        $this->cache->expects($this->once())
                    ->method('fetch')
                    ->with(md5('Neptune\Tests\Cache\FooCacheable:configUsingMethod'))
                    ->will($this->returnValue(false));
        $this->cache->expects($this->never())
                    ->method('save');
        $this->obj->setCache($this->cache);
        //mock a class that is called inside config(). If it isn't
        //called, then the config() has not run
        $config = $this->getMock('Neptune\Config\Config');
        $config->expects($this->never())
               ->method('get');
        $this->obj->setConfig($config);
        $this->assertSame(false, $this->obj->configUsingMethodCached());
    }

    public function testDifferentArgsDifferentResults()
    {
        $key1 = $this->createKey([1]);
        $key2 = $this->createKey([2]);
        $map = [
            [$key1, 'Foo1'],
            [$key2, 'Foo2'],
        ];
        $this->cache->expects($this->exactly(2))
                    ->method('fetch')
                    ->will($this->returnValueMap($map));
        $this->cache->expects($this->never())
                    ->method('save');
        $this->obj->setCache($this->cache);
        $this->assertSame('Foo1', $this->obj->fooCached(1));
        $this->assertSame('Foo2', $this->obj->fooCached(2));
    }
}

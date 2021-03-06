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

    public function testCallMethod()
    {
        $key = md5('Neptune\Tests\Cache\FooCacheable:foo');
        $this->cache->expects($this->once())
                    ->method('fetch')
                    ->with($key)
                    ->will($this->returnValue(false));
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
                    ->with($key)
                    ->will($this->returnValue(false));
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

    public function testMethodIsNotCalledWhenCacheReturnsNull()
    {
        $this->cache->expects($this->once())
                    ->method('fetch')
                    ->with(md5('Neptune\Tests\Cache\FooCacheable:configUsingMethod'))
                    ->will($this->returnValue(null));
        $this->cache->expects($this->never())
                    ->method('save');
        $this->obj->setCache($this->cache);
        //mock a class that is called inside config(). If it isn't
        //called, then the config() has not run
        $config = $this->getMock('Neptune\Config\Config');
        $config->expects($this->never())
               ->method('get');
        $this->obj->setConfig($config);
        $this->assertSame(null, $this->obj->configUsingMethodCached());
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

    public function testSetDefaultLifetime()
    {
        $this->assertSame($this->obj, $this->obj->setDefaultLifetime(20));
    }

    public function testLifetime()
    {
        $this->assertSame($this->obj, $this->obj->lifetime(80));
    }

    public function testDefaultLifetimeUsedToSaveCache()
    {
        $this->cache->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue(false));
        $this->cache->expects($this->exactly(2))
                    ->method('save')
                    ->withConsecutive(
                        [$this->anything(), $this->anything(), 0],
                        [$this->anything(), $this->anything(), 20]
                    );
        $this->obj->setCache($this->cache);

        $this->obj->fooCached();

        $this->obj->setDefaultLifetime(20);
        $this->obj->fooCached();
    }

    public function testLifetimeHasPrecedenceOverDefault()
    {
        $this->cache->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue(false));
        $this->cache->expects($this->exactly(2))
                    ->method('save')
                    ->withConsecutive(
                        [$this->anything(), $this->anything(), 30],
                        [$this->anything(), $this->anything(), 20]
                    );
        $this->obj->setCache($this->cache);
        $this->obj->setDefaultLifetime(20);

        $this->obj->lifetime(30)->fooCached();

        //30s lifetime should have been reset. It's now the default, 20
        $this->obj->fooCached();
    }
}

<?php

namespace Neptune\Tests\Routing;

use Neptune\Routing\Url;
use Neptune\Routing\Router;
use Neptune\Routing\Route;

use Symfony\Component\HttpFoundation\Request;

/**
 * RouterTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->router = new Router(new Url('myapp.local/'));
    }

    protected function routeTest($route, $url)
    {
        $req = Request::create($url);

        return $route->test($req);
    }

    protected function match($pathinfo, $method = 'GET')
    {
        $request = Request::create($pathinfo);
        $request->setMethod($method);

        return $this->router->match($request);
    }

    public function testRouteReturnsRoute()
    {
        $this->assertInstanceOf('\Neptune\Routing\Route', $this->router->route('/url'));
    }

    public function testCatchAllReturnRoute()
    {
        $r = $this->router->catchAll('foo');
        $this->assertInstanceOf('\Neptune\Routing\Route', $r);
        $this->assertSame('.*', $r->getUrl());
        $this->assertSame(['neptune.catch_all' => '.*'], $this->router->getNames());
    }

    public function testMissingSlash()
    {
        $r = $this->router->route('test');
        $this->assertSame('/test', $r->getUrl());
    }

    public function testMatch()
    {
        $this->router->route('/test', 'test', 'index');
        $expected = ['test', 'index', []];
        $this->assertSame($expected, $this->match('/test'));
    }

    public function testMatchThrowsExceptionNoAction()
    {
        $msg = 'No route found that matches "foo"';
        $this->setExpectedException('\Neptune\Routing\RouteNotFoundException', $msg);
        $this->match('foo');
    }

    public function testCatchAll()
    {
        $this->router->route('/test', 'test', 'index');
        $this->router->catchAll('foo');
        $expected = ['foo', 'index', []];
        $this->assertSame($expected, $this->match('/foo'));
    }

    public function testGetRoutes()
    {
        $this->router->catchAll('foo');
        $routes = $this->router->getRoutes();
        $this->assertInstanceOf('\Neptune\Routing\Route', $routes[0]);
        $this->assertSame('.*', $routes[0]->getUrl());
    }

    protected function setUpTestModule($prefix)
    {
        $module = new TestModule($prefix);
        $neptune = $this->getMockBuilder('Neptune\Core\Neptune')
                        ->disableOriginalConstructor()
                        ->getMock();
        $this->router->routeModule($module, $neptune);
    }

    public function testAddModule()
    {
        //TestModule defines a route with '/$prefix/login
        $this->setUpTestModule('foo');
        $names = [
            'test-module:_unknown_0' => '/foo/login',
            'test-module:secret' => '/foo/secret'
        ];
        $this->assertSame($names, $this->router->getNames());

        $routes = $this->router->getRoutes();

        $this->assertSame(2, count($routes));

        $route = $routes[0];
        $this->assertInstanceOf('\Neptune\Routing\Route', $route);
        $this->assertSame('/foo/login', $route->getUrl());

        $route = $routes[1];
        $this->assertInstanceOf('\Neptune\Routing\Route', $route);
        $this->assertSame('/foo/secret', $route->getUrl());
    }

    public function testAddModuleDoesNotAffectFutureNames()
    {
        $this->setUpTestModule('bar');
        $this->router->route('foo');
        $names = [
            'test-module:_unknown_0' => '/bar/login',
            'test-module:secret' => '/bar/secret',
            '_unknown_1' => '/foo',
        ];
        $this->assertSame($names, $this->router->getNames());
    }

    public function testGetAndSetCache()
    {
        $driver = $this->getMock('\Doctrine\Common\Cache\Cache');
        $this->router->setCache($driver);
        $this->assertSame($driver, $this->router->getCache());
    }

    public function testName()
    {
        $this->assertInstanceOf('\Neptune\Routing\Router', $this->router->name('route'));
        $this->assertSame('route', $this->router->getName());
    }

    public function testNameIsAddedToRouteThenUnset()
    {
        $this->router->name('foo');
        $this->assertSame('foo', $this->router->getName());
        $r = $this->router->route('/test', 'foo');
        $this->assertNull($this->router->getName());
        $names = ['foo' => $r->getUrl()];
        $this->assertSame($names, $this->router->getNames());

        //second route should be given a name automatically
        $this->router->route('/second', 'bar');
        $this->assertNull($this->router->getName());
        $names['_unknown_0'] = '/second';
        $this->assertSame($names, $this->router->getNames());
    }

    public function testGetNamesReturnsEmptyArray()
    {
        $this->assertSame([], $this->router->getNames());
    }

    public function testUrlSimple()
    {
        $this->router->name('simple')->route('/url', 'controller');
        $this->assertSame('http://myapp.local/url', $this->router->url('simple'));
    }

    public function testUrlSimpleFtp()
    {
        $this->router->name('ftp')->route('/url', 'controller');
        $this->assertSame('ftp://myapp.local/url', $this->router->url('ftp', [], 'ftp'));
    }

    public function testUrlWithArgs()
    {
        $this->router->name('args')->route('/url/:var/:second', 'controller');
        $this->assertSame('http://myapp.local/url/foo/bar', $this->router->url('args', ['var' => 'foo', 'second' => 'bar']));
    }

    public function testUrlWithArgsFtp()
    {
        $this->router->name('args_ftp')->route('/url/:var/:second', 'controller');
        $this->assertSame('ftp://myapp.local/url/foo/bar', $this->router->url('args_ftp',
            ['var' => 'foo', 'second' => 'bar'], 'ftp'));
    }

    public function testUrlOptionalArgs()
    {
        $this->router->name('opt_args')->route('/url(/:var(/:second))');
        $this->assertSame('http://myapp.local/url/foo',
            $this->router->url('opt_args', ['var' => 'foo']));
    }

    public function testUrlWithFalseyArgs()
    {
        $this->router->name('opt_args')->route('/url(/:var(/:second)).json');
        $this->assertSame('http://myapp.local/url.json',
            $this->router->url('opt_args', ['var' => null]));
    }

    public function testUrlNoOptionalArgs()
    {
        $this->router->name('no_opt_args')->route('/url/(:var(/:second))', 'controller');
        $this->assertSame('http://myapp.local/url', $this->router->url('no_opt_args'));
    }

    public function testUrlAppendedGetVariables()
    {
        $this->router->name('get')->route('/get/:id', 'getController');
        $args = ['id' => 34, 'foo' => 'bar', 'baz' => 'qoz'];
        $actual = $this->router->url('get', $args);
        $this->assertSame('http://myapp.local/get/34?foo=bar&baz=qoz', $actual);
    }

    public function testUrlThrowsExceptionWithNoNames()
    {
        $this->setExpectedException('\Exception', 'No routes defined');
        $this->router->url('get');
    }

    public function testUrlThrowsExceptionUnknownName()
    {
        $this->router->name('foo')->route('/foo', 'bar');
        $this->setExpectedException('\Exception', "Unknown route 'get'");
        $this->router->url('get');
    }

    public function testUrlGetsNamesFromCache()
    {
        $driver = $this->getMock('\Doctrine\Common\Cache\Cache');
        $driver->expects($this->exactly(1))
               ->method('fetch')
               ->with(Router::CACHE_KEY_NAMES)
               ->will($this->returnValue(['get' => '/get/:id']));
        $this->router->setCache($driver);
        $this->assertSame('http://myapp.local/get/42', $this->router->url('get', ['id' => 42]));
    }

    public function testUrlThrowsExceptionWithInvalidCache()
    {
        $driver = $this->getMock('\Doctrine\Common\Cache\Cache');
        $driver->expects($this->exactly(1))
               ->method('fetch')
               ->with(Router::CACHE_KEY_NAMES)
               ->will($this->returnValue('foo'));
        $this->router->setCache($driver);
        $this->setExpectedException('\Exception', 'Cache value \'Router.names\' is not an array');
        $this->router->url('get');
    }

    public function testUrlThrowsExceptionWithCacheMiss()
    {
        $driver = $this->getMock('\Doctrine\Common\Cache\Cache');
        $driver->expects($this->exactly(1))
               ->method('fetch')
               ->with(Router::CACHE_KEY_NAMES);
        $this->router->setCache($driver);
        $this->setExpectedException('\Exception', 'No routes defined');
        $this->router->url('get');
    }

    public function testMatchCached()
    {
        $cache = $this->getMock('\Doctrine\Common\Cache\Cache');
        $route = ['::controller.foo', 'index', []];
        $cache->expects($this->once())
              ->method('fetch')
              ->with('Router./fooGET')
              ->will($this->returnValue($route));
        $cache->expects($this->never())
              ->method('save');
        $this->router->setCache($cache);
        $request = Request::create('/foo');
        $this->assertSame($route, $this->router->matchCached($request));
    }

    public function testMatchCachedThrowsExceptionOnInvalidCache()
    {
        $cache = $this->getMock('\Doctrine\Common\Cache\Cache');
        $cache->expects($this->once())
              ->method('fetch')
              ->with('Router./fooGET')
              ->will($this->returnValue('foo'));
        $this->router->setCache($cache);
        $request = Request::create('/foo');
        $this->setExpectedException('\Exception');
        $this->router->matchCached($request);
    }

    public function testRouteIsCachedOnSuccess()
    {
        $this->router->name('test-route')->route('/test', 'module:controller', 'index');

        $cache = $this->getMock('\Doctrine\Common\Cache\Cache');
        $cache->expects($this->exactly(2))
            ->method('save')
            //router saves route names and action
            ->with($this->logicalOr(
                'Router./testGET',
                'Router.names'
            ),
            $this->logicalOr(
                ['test-route' => '/test'],
                ['module:controller', 'index', []]
            ));

        $this->router->setCache($cache);

        $expected = ['module:controller', 'index', []];
        $this->assertSame($expected, $this->match('/test'));
    }

    public function testRouteWithVariableIsNotCached()
    {
        $this->router->route('/test/:var', 'module:controller', 'index');

        $cache = $this->getMock('\Doctrine\Common\Cache\Cache');
        //a route with a variable should not be cached
        $cache->expects($this->never())
               ->method('save');
        $this->router->setCache($cache);

        $expected = ['module:controller', 'index', ['var' => 'foo']];
        $this->assertSame($expected, $this->match('/test/foo'));
    }

    public function testMatchCachedReturnsFalseWithNoCache()
    {
        $request = Request::create('/foo');
        $this->assertFalse($this->router->matchCached($request));
    }

}

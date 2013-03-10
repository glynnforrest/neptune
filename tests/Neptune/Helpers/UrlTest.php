<?php

namespace Neptune\Helpers;

use Neptune\Helpers\Url;
use Neptune\Core\Dispatcher;
use Neptune\Core\Config;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * UrlTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class UrlTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Config::create('testing');
		Config::set('root_url', 'myapp.local');
	}

	public function tearDown() {
		Config::unload();
	}

	public function testTo() {
		$this->assertEquals('http://myapp.local/404', Url::to('404'));
	}

	public function testToFtp() {
		$this->assertEquals('ftp://myapp.local/file', Url::to('file', 'ftp'));
	}

	public function testToAbsolute() {
		$this->assertEquals('http://google.com', Url::to('http://google.com'));
		$this->assertEquals('https://google.com', Url::to('https://google.com'));
		$this->assertEquals('ftp://google.com', Url::to('ftp://google.com'));
	}

	public function testSimpleRoute() {
		$d = Dispatcher::getInstance()->clearRoutes();
		$d->route('/url', 'controller')->name('simple');
		$this->assertEquals('http://myapp.local/url', Url::toRoute('simple'));
	}

	public function testSimpleFtpRoute() {
		$d = Dispatcher::getInstance()->clearRoutes();
		$d->route('/url', 'controller')->name('ftp');
		$this->assertEquals('ftp://myapp.local/url', Url::toRoute('ftp', array(), 'ftp'));
	}

	public function testRouteArgs() {
		$d = Dispatcher::getInstance()->clearRoutes();
		$d->route('/url/:var/:second', 'controller')->name('args');
		$this->assertEquals('http://myapp.local/url/foo/bar', Url::toRoute('args', array('var' => 'foo', 'second' => 'bar')));
	}

	public function testRouteArgsFtp() {
		$d = Dispatcher::getInstance()->clearRoutes();
		$d->route('/url/:var/:second', 'controller')->name('args_ftp');
		$this->assertEquals('ftp://myapp.local/url/foo/bar', Url::toRoute('args_ftp',
			array('var' => 'foo', 'second' => 'bar'), 'ftp'));
	}

	public function testOptionalArgs() {
		$d = Dispatcher::getInstance()->clearRoutes();
		$d->route('/url/(:var(/:second))', 'controller')->name('opt_args');
		$this->assertEquals('http://myapp.local/url/foo',
			Url::toRoute('opt_args', array('var' => 'foo')));
	}

	public function testNoOptionalArgs() {
		$d = Dispatcher::getInstance()->clearRoutes();
		$d->route('/url/(:var(/:second))', 'controller')->name('no_opt_args');
		$this->assertEquals('http://myapp.local/url', Url::toRoute('no_opt_args'));
	}
}
?>

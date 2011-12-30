<?php

namespace neptune\helpers;

use neptune\helpers\Url;
use neptune\core\Dispatcher;
use neptune\core\Config;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * UrlTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class UrlTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Config::bluff('testing');
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
		$d = Dispatcher::getInstance()->clear()->reset();
		$d->route('/url', array('name' => 'first route'));
		$this->assertEquals('http://myapp.local/url', Url::toRoute('first route'));
	}

	public function testSimpleFtpRoute() {
		$d = Dispatcher::getInstance()->clear()->reset();
		$d->route('/url', array('name' => 'first route'));
		$this->assertEquals('ftp://myapp.local/url', Url::toRoute('first route', array(), 'ftp'));
	}

	public function testRouteArgs() {
		$d = Dispatcher::getInstance()->clear()->reset();
		$d->route('/url/:var/:second', array('name' => 'first route'));
		$this->assertEquals('http://myapp.local/url/foo/bar', Url::toRoute('first route', array('var' => 'foo', 'second' => 'bar')));
	}

	public function testRouteArgsFtp() {
		$d = Dispatcher::getInstance()->clear()->reset();
		$d->route('/url/:var/:second', array('name' => 'first route'));
		$this->assertEquals('ftp://myapp.local/url/foo/bar', Url::toRoute('first route', array('var' => 'foo', 'second' => 'bar'), 'ftp'));
	}

	
}
?>

<?php

namespace Neptune\Tests\Helpers;

use Neptune\Helpers\Url;
use Neptune\Core\Config;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * UrlTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class UrlTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$c = Config::create('testing');
		$c->set('root_url', 'myapp.local/');
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

}

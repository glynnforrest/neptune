<?php

namespace Neptune\Tests\Helpers;

require_once __DIR__ . '/../../../bootstrap.php';

use Neptune\Helpers\RequestHelper;

use Symfony\Component\HttpFoundation\Request;

/**
 * RequestHelperTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class RequestHelperTest extends \PHPUnit_Framework_TestCase {

	public function testGetAndSet() {
		$req = Request::create('foo');
		$obj = new RequestHelper($req);
		$this->assertSame($req, $obj->getRequest());
		$req2 = Request::create('bar');
		$obj->setRequest($req2);
		$this->assertSame($req2, $obj->getRequest());
		$this->assertNotSame($req, $obj->getRequest());
	}

	public function pathProvider() {
		return array(
			array('/', '/'),
			array('a', 'a'),
			array('.', '.'),
			array('foo', 'foo'),
			array('app/foo.', 'app/foo'),
			array('foo.html', 'foo'),
			array('.css', '.css'),
			array('file/test.css/', 'file/test'),
			array('file/test.css.html', 'file/test.css'),
			array('foo?bar=baz', 'foo'),
			array('foo.js?bar=baz', 'foo'),
			array('foo/.js?bar=baz', 'foo'),
			array('foo/file.js/test.blah?bar=baz', 'foo/file.js/test'),
		);
	}

	/**
	 * @dataProvider pathProvider()
	 */
	public function testGetBarePath($url, $expected) {
		$helper = new RequestHelper(Request::create($url));
		$this->assertSame($expected, $helper->getBarePath());
	}

	public function formatProvider() {
		return array(
			array('/', 'html'),
			array('a', 'html'),
			array('.', 'html'),
			array('foo', 'html'),
			array('app/foo.', 'html'),
			array('foo.html', 'html'),
			array('.css', 'html'),
			array('a.css', 'css'),
			array('file/test.css/', 'css'),
			array('file/test.css.html', 'html'),
			array('foo?bar=baz', 'html'),
			array('foo.js?bar=baz', 'js'),
			array('foo/.js?bar=baz', 'js'),
			array('foo/file.js/test.blah?bar=baz', 'blah'),
		);
	}

	/**
	 * @dataProvider formatProvider()
	 */
	public function testGetBestFormatNoAcceptHeader($url, $expected) {
		$helper = new RequestHelper(Request::create($url));
		$this->assertSame($expected, $helper->getBestFormat());
	}

}
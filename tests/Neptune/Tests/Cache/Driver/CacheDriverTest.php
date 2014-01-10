<?php

namespace Neptune\Tests\Cache\Driver;

/**
 * FileDriverTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class CacheDriverTest extends \PHPUnit_Framework_TestCase {

	public function cacheDataProvider() {
		return array(
			array('foo', 'bar'),
			array('1', 1),
			array(1, 1),
			array('an-array', array()),
			array('another-array', array(1, '2', array(), new \stdClass())),
			array('object', new \stdClass()),
			array('false', false)
		);
	}

}
<?php

namespace neptune\security;

use neptune\core\Config;
use neptune\security\drivers\DebugDriver;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * SecurityFactoryTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class SecurityFactoryTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Config::bluff('unittest');
		Config::set('security', array('one' => 'debug', 'two' => 'fake'));
	}

	public function tearDown() {
		Config::unload();
	}

	public function testGet() {
		$this->assertTrue(SecurityFactory::getSecurity() instanceof DebugDriver);
		$this->assertTrue(SecurityFactory::getSecurity('one') instanceof DebugDriver);
	}

	public function testGetBadConfig() {
		$this->setExpectedException('\\neptune\\exceptions\\ConfigKeyException');
		Config::set('security', array());
		SecurityFactory::getSecurity('wrong');
	}

	public function testGetUndefinedDriver() {
		$this->setExpectedException('\\neptune\\exceptions\\DriverNotFoundException');
		SecurityFactory::getSecurity('two');
	}
	
}
?>
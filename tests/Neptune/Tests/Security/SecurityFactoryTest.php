<?php

namespace Neptune\Security;

use Neptune\Core\Config;
use Neptune\Security\Drivers\DebugDriver;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * SecurityFactoryTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class SecurityFactoryTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Config::create('unittest');
		Config::set('security', array('one' => 'debug', 'two' => 'fake'));
	}

	public function tearDown() {
		Config::unload();
	}

	public function testGet() {
		$this->assertTrue(SecurityFactory::getDriver() instanceof DebugDriver);
		$this->assertTrue(SecurityFactory::getDriver('one') instanceof DebugDriver);
	}

	public function testGetBadConfig() {
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
		Config::set('security', array());
		SecurityFactory::getDriver('wrong');
	}

	public function testGetUndefinedDriver() {
		$this->setExpectedException('\\Neptune\\Exceptions\\DriverNotFoundException');
		SecurityFactory::getDriver('two');
	}

}
?>

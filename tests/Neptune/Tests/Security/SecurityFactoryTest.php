<?php

namespace Neptune\Tests\Security;

use Neptune\Security\SecurityFactory;
use Neptune\Core\Config;
use Neptune\Security\Drivers\DebugDriver;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * SecurityFactoryTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class SecurityFactoryTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$c = Config::create('unittest');
		$c->set('security', array('one' => 'debug', 'two' => 'fake'));
	}

	public function tearDown() {
		Config::unload();
	}

	public function testGetDriver() {
		$this->assertTrue(SecurityFactory::getDriver() instanceof DebugDriver);
		$this->assertTrue(SecurityFactory::getDriver('one') instanceof DebugDriver);
		$this->assertTrue(SecurityFactory::getDriver() === SecurityFactory::getDriver('one'));
	}

	public function testGetDriverPrefix() {
		$c = Config::create('prefix');
		$c->set('security', array('default' => 'debug', 'second' => 'debug'));
		$this->assertTrue(SecurityFactory::getDriver('prefix#default') instanceof DebugDriver);
		$this->assertTrue(SecurityFactory::getDriver('prefix#second') instanceof DebugDriver);
		$this->assertTrue(SecurityFactory::getDriver('prefix#') instanceof DebugDriver);
		$this->assertTrue(SecurityFactory::getDriver('prefix#') === SecurityFactory::getDriver('prefix#default'));
	}

	public function testGetBadConfig() {
		$this->setExpectedException('\\Neptune\\Exceptions\\ConfigKeyException');
		$c = Config::load('unittest');
		$c->set('security', array());
		SecurityFactory::getDriver('wrong');
	}

	public function testGetUndefinedDriver() {
		$this->setExpectedException('\\Neptune\\Exceptions\\DriverNotFoundException');
		SecurityFactory::getDriver('two');
	}

}

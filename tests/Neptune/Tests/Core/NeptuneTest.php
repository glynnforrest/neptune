<?php

namespace Neptune\Tests\Core;

use Neptune\Core\Neptune;
use Neptune\Core\Config;

use Temping\Temping;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * NeptuneTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class NeptuneTest extends \PHPUnit_Framework_TestCase {

	protected $neptune;

	public function setUp() {
		$this->neptune = Neptune::getInstance();
		$this->neptune->reset();
	}

	public function tearDown() {
		Temping::getInstance()->reset();
		Config::unload();
	}

	public function testGetAndSet() {
		$this->neptune->set('my-component', function() {
			return new \stdClass();
		});
		$component = $this->neptune->get('my-component');
		$this->assertTrue($component instanceof \stdClass);
	}

	public function testGetThrowsExceptionNotRegistered() {
		$this->setExpectedException('\Neptune\Core\ComponentException');
		$this->neptune->get('my-component');
	}

	public function testGetThrowsExceptionNotCallable() {
		$this->neptune->set('my-component', 'no-a-function');
		$this->setExpectedException('\Neptune\Core\ComponentException');
		$this->neptune->get('my-component');
	}

	public function testGetCreatesNewObjectEveryTime() {
		$this->neptune->set('my-component', function() {
			return new \stdClass();
		});
		$one = $this->neptune->get('my-component');
		$two = $this->neptune->get('my-component');
		$this->assertNotSame($one, $two);
	}

	public function testGetAndSetSingleton() {
		$this->neptune->setSingleton('my-singleton', function() {
			return new \stdClass();
		});
		$one = $this->neptune->get('my-singleton');
		$two = $this->neptune->get('my-singleton');
		$this->assertSame($one, $two);
	}

	public function testLoadEnv() {
		$temp = Temping::getInstance();
		$config_file = '<?php';
		$config_file .= <<<END
		return array(
			'foo' => 'override',
		);
END;
		$temp->create('config/env/development.php', $config_file);
		$env_file = file_get_contents(__DIR__ . '/etc/sample_env.php');
		$temp->create('app/env/development.php', $env_file);
		$c = Config::create('neptune');
		$c->set('foo', 'default');
		$c->set('dir.root', $temp->getDirectory());
		$this->assertEquals('default', $c->get('foo'));
		$this->assertFalse(defined('SOME_CONSTANT'));
		//loadEnv should call Config::loadEnv and include app/env/<env>.php
		$this->neptune->loadEnv('development');
		$this->assertTrue(defined('SOME_CONSTANT'));
		$this->assertEquals('override', $c->get('foo'));
	}

	public function testLoadEnvDefaultNoArg() {
		$temp = Temping::getInstance();
		$config_file = '<?php';
		$config_file .= <<<END
		return array(
			'foo' => 'override',
		);
END;
		$temp->create('config/env/development.php', $config_file);
		$env_file = file_get_contents(__DIR__ . '/etc/sample_env.php');
		$temp->create('app/env/development.php', $env_file);
		$c = Config::create('neptune');
		$c->set('foo', 'default');
		$c->set('dir.root', $temp->getDirectory());
		$this->assertEquals('default', $c->get('foo'));
		$c->set('env', 'development');
		$this->neptune->loadEnv();
		$this->assertEquals('override', $c->get('foo'));
	}

	public function testLoadEnvNoArgThrowsException() {
		$c = Config::create('neptune');
		$this->setExpectedException('\Neptune\Exceptions\ConfigKeyException');
		$this->neptune->loadEnv();
	}

}

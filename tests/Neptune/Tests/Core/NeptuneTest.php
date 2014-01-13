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
	protected $temp;

	public function setUp() {
		$this->neptune = Neptune::getInstance();
		$this->temp = new Temping();
	}

	public function tearDown() {
		$this->neptune->reset();
		$this->temp->reset();
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

	public function testLoadAndGetEnv() {
		$config_file = '<?php';
		$config_file .= <<<END
		return array(
			'foo' => 'override',
		);
END;
		$this->temp->create('config/env/production.php', $config_file);
		$this->temp->create('app/env/production.php', file_get_contents(__DIR__ . '/etc/sample_env.php'));
		$c = Config::create('neptune');
		$c->set('foo', 'default');
		$c->set('dir.root', $this->temp->getDirectory());
		$this->assertEquals('default', $c->get('foo'));
		$this->assertFalse(defined('SOME_CONSTANT'));
		//loadEnv should call Config::loadEnv and include app/env/<env>.php
		$this->neptune->loadEnv('production');
		$this->assertTrue(defined('SOME_CONSTANT'));
		$this->assertEquals('override', $c->get('foo'));
		$this->assertSame('production', $this->neptune->getEnv());
	}

	public function testLoadAndGetEnvDefaultNoArg() {
		$config_file = '<?php';
		$config_file .= <<<END
		return array(
			'foo' => 'override',
		);
END;
		$this->temp->create('config/env/development.php', $config_file);
		$this->temp->create('app/env/development.php', file_get_contents(__DIR__ . '/etc/sample_env.php'));
		$c = Config::create('neptune');
		$c->set('foo', 'default');
		$c->set('dir.root', $this->temp->getDirectory());
		$this->assertEquals('default', $c->get('foo'));
		$c->set('env', 'development');
		$this->neptune->loadEnv();
		$this->assertEquals('override', $c->get('foo'));
		$this->assertSame('development', $this->neptune->getEnv());
	}

	public function testLoadEnvNoArgThrowsException() {
		$c = Config::create('neptune');
		$this->setExpectedException('\Neptune\Exceptions\ConfigKeyException');
		$this->neptune->loadEnv();
	}

	public function testGetEnvReturnsNullWithNoEnv() {
		$this->assertNull($this->neptune->getEnv());
	}

}

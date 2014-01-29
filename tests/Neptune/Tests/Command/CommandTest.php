<?php

namespace Neptune\Tests\Command;

require_once __DIR__ . '/../../../bootstrap.php';

use Neptune\Core\Config;
use Neptune\Core\Neptune;
use Neptune\Tests\Command\EmptyCommand;
use Neptune\Console\Application;

use Symfony\Component\Console\Tester\CommandTester;

/**
 * CommandTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CommandTest extends \PHPUnit_Framework_TestCase {

	protected $config;
	protected $command;
    protected $neptune;

	public function setup() {
		$this->config = Config::create('neptune');
		$this->config->set('dir.root', '/path/to/root/');
        $this->neptune = new Neptune($this->config);
		$application = new Application($this->config);
		$application->add(new EmptyCommand($this->neptune, $this->config));
		$this->command = $application->find('empty');
	}

	public function testGetRootDirectory() {
		$expected = $this->config->get('dir.root');
		$this->assertSame($expected, $this->command->getRootDirectory());
	}

	public function testGetRootDirectoryAppendsTrailingSlash() {
		$this->config->set('dir.root', '/no/trailing/slash');
		$this->assertSame('/no/trailing/slash/', $this->command->getRootDirectory());
	}

	public function testGetModuleDirectory() {
		$modules = array(
			'my-app' => 'app/MyApp/');
		$this->config->set('modules', $modules);
		$expected = $this->config->get('dir.root') . 'app/MyApp/';
		$this->assertSame($expected, $this->command->getModuleDirectory('my-app'));
		//check it is an absolute path
	}

	public function testGetDefaultModule() {
		$modules = array(
			'my-app' => 'app/MyApp/',
			'other-module' => 'app/OtherModel/');
		$this->config->set('modules', $modules);
		$this->assertSame('my-app', $this->command->getDefaultModule());
	}

	public function testGetModuleNamespace() {
		$config = Config::create('my-app');
		$config->set('namespace', 'MyApp');
		$this->assertSame('MyApp', $this->command->getModuleNamespace('my-app'));
		$config->set('namespace', 'Changed');
		$this->assertSame('Changed', $this->command->getModuleNamespace('my-app'));
	}

}

<?php

namespace Neptune\Tests\Command;

require_once __DIR__ . '/../../../bootstrap.php';

use Neptune\Core\Config;
use Neptune\Tests\Command\EmptyCommand;

use Symfony\Component\Console\Tester\CommandTester;
use Neptune\Console\Application;

/**
 * CommandTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CommandTest extends \PHPUnit_Framework_TestCase {

	protected $config;
	protected $command;

	public function setup() {
		$this->config = Config::create('neptune');
		$this->config->set('dir.root', '/path/to/root/');
		$this->config->set('namespace', 'MyEmptyApp');
		$this->command = new EmptyCommand($this->config);
	}

	public function testGetRootDirectory() {
		$expected = $this->config->get('dir.root');
		$this->assertSame($expected, $this->command->getRootDirectory());
	}

	public function testGetRootDirectoryAppendsTrailingSlash() {
		$this->config->set('dir.root', '/no/trailing/slash');
		$this->assertSame('/no/trailing/slash/', $this->command->getRootDirectory());
	}

	public function testGetAppDirectory() {
		$expected = $this->config->get('dir.root') . 'app/MyEmptyApp/';
		$this->assertSame($expected, $this->command->getAppDirectory());
	}

	public function testNamespace() {
		$this->assertSame('MyEmptyApp', $this->command->getNamespace());
	}


}

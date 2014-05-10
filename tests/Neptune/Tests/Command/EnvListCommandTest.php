<?php

namespace Neptune\Tests\Command;

require_once __DIR__ . '/../../../bootstrap.php';

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;

use Neptune\Config\NeptuneConfig;
use Neptune\Core\Neptune;
use Neptune\Command\EnvListCommand;
use Neptune\Console\Console;

use Temping\Temping;

/**
 * EnvListCommandTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EnvListCommandTest extends \PHPUnit_Framework_TestCase {

	protected $tester;
	protected $temping;
	protected $config;
    protected $neptune;

	public function setup() {

		$this->temping = new Temping();
		$this->temping->createDirectory('config/env');
		$this->config = new NeptuneConfig($this->temping->getDirectory(), false);
        $this->neptune = new Neptune($this->config);

		$application = new Application();
		$application->add(new EnvListCommand($this->neptune, $this->config));
		$command = $application->find('env:list');
		$this->tester = new CommandTester($command);
	}

	public function tearDown() {
		$this->temping->reset();
	}

	public function testListNoEnvs() {
		$this->tester->execute(array());
		$this->assertSame('', $this->tester->getDisplay());
	}

	public function testListOneEnv() {
		$this->temping->create('config/env/development.php');
		$this->tester->execute(array());
		$this->assertSame("development\n", $this->tester->getDisplay(true));
	}

	public function testListMultipleEnvs() {
		//create a mock project folder containing some stub config files
		$this->temping->create('config/env/production.php');
		$this->temping->create('config/env/development.php');
		$this->tester->execute(array());
		$this->assertSame("development\nproduction\n", $this->tester->getDisplay(true));
	}

	public function testCurrentEnvHighlighted() {
		//set the Console helper within EnvListCommand to give raw
		//output so we can look at the <tags>
		Console::outputRaw();
		$this->config->set('env', 'production');
		$this->temping->create('config/env/production.php');
		$this->temping->create('config/env/development.php');
		$this->tester->execute(array());
		$expected = "development\n<info>production</info>\n";
		$this->assertSame($expected, $this->tester->getDisplay(true));
	}

}

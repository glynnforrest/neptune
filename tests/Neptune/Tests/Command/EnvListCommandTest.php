<?php

namespace Neptune\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;

use Neptune\Config\Config;
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
        $this->neptune = new Neptune($this->temping->getDirectory());
        $this->config = new Config();
        $this->neptune['config'] = $this->config;

		$application = new Application();
		$application->add(new EnvListCommand($this->neptune));
		$command = $application->find('env:list');
		$this->tester = new CommandTester($command);
	}

	public function tearDown() {
		$this->temping->reset();
	}

	public function testListNoEnvs() {
        $this->setExpectedException('\Exception');
		$this->tester->execute(array());
	}

	public function testListOneEnv() {
		$this->temping->create('config/env/development.php');
		$this->tester->execute(array());
		$this->assertSame("development\n", $this->tester->getDisplay(true));
	}

	public function testListMultipleEnvs() {
		//create a mock project folder containing some stub config files
		$this->temping->create('config/env/production.yml');
		$this->temping->create('config/env/development.php');
		$this->tester->execute(array());
		$this->assertSame("development\nproduction\n", $this->tester->getDisplay(true));
	}

    /**
     * Mock output interface to check for tags, as they won't appear
     * in getDisplay().
     */
    public function testCurrentEnvHighlighted()
    {
        $this->neptune->setEnv('production');
        $this->temping->create('config/env/production.php');
        $this->temping->create('config/env/development.yml');

        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $output->expects($this->exactly(2))
            ->method('writeln')
            ->withConsecutive(['development'], ['<info>production</info>']);

        $command = new EnvListCommand($this->neptune);
        $command->run($input, $output);
    }

}

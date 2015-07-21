<?php

namespace Neptune\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Neptune\Service\AbstractModule;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Neptune\Database\FixtureLoader;

/**
 * DatabaseFixturesRunCommand.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DatabaseFixturesRunCommand extends DatabaseMigrateListCommand
{
    protected $name = 'database:fixtures:run';
    protected $description = 'Run fixtures';

    protected function configure()
    {
        parent::configure();
        $this->addOption(
            'module',
            'm',
            InputOption::VALUE_REQUIRED,
            'Only run fixtures for a certain module'
        )->addOption(
            'append',
            '',
            InputOption::VALUE_NONE,
            'Don\'t empty database tables'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = $this->neptune['db'];
        $loader = new FixtureLoader();
        $loader->setLogger(new ConsoleLogger($output));

        $fixtures = $input->getOption('module') ?
                  $this->getModuleFixtures($this->neptune->getModule($input->getOption('module'))) :
                  $this->getAllFixtures();
        $count = 0;
        foreach ($fixtures as $fixture) {
            ++$count;
            $loader->addFixture($fixture);
        }

        $loader->run($db, $input->getOption('append'));
        $inflection = $count === 1 ? 'fixture' : 'fixtures';
        $output->writeln(sprintf('Ran <info>%s</info> %s.', $count, $inflection));
    }

    protected function getModuleFixtures(AbstractModule $module)
    {
        $namespace = $module->getNamespace().'\\Fixtures\\';
        $directory = $module->getDirectory().'Fixtures/';
        if (!is_dir($directory)) {
            return [];
        }

        $fixtures = [];
        $files = new \DirectoryIterator($directory);
        foreach ($files as $file) {
            if (!$file->isFile() || substr($file->getFilename(), -4) !== '.php') {
                continue;
            }
            $class = $namespace.$file->getBasename('.php');
            $r = new \ReflectionClass($class);
            if (!$r->isSubclassOf('ActiveDoctrine\\Fixture\\FixtureInterface') || $r->isAbstract()) {
                continue;
            }
            $fixtures[] = $r->newInstance();
        }

        return $fixtures;
    }

    protected function getAllFixtures()
    {
        $fixtures = [];
        foreach ($this->neptune->getModules() as $module) {
            $fixtures = array_merge($fixtures, $this->getModuleFixtures($module));
        }

        return $fixtures;
    }
}

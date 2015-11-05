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
class DatabaseFixturesRunCommand extends Command
{
    protected $name = 'database:fixtures:run';
    protected $description = 'Run fixtures';

    protected function configure()
    {
        parent::configure();
        $this->addOption(
            'modules',
            'm',
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'Only run fixtures for the given modules'
        )->addOption(
            'exclude-modules',
            'x',
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'Exclude fixtures in the given modules'
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
        $loader = new FixtureLoader($this->neptune);
        $loader->setLogger(new ConsoleLogger($output));

        $fixtures = [];
        foreach ($this->getInputModules($input) as $module) {
            $fixtures = array_merge($fixtures, $this->getModuleFixtures($module));
        }

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

    /**
     * Get modules from the given input.
     * --modules takes priority over --exclude-modules.
     * Defaults to all modules.
     *
     * @param InputInterface $input
     */
    protected function getInputModules(InputInterface $input)
    {
        if ($input->getOption('modules')) {
            return array_map(function ($moduleName) {
                return $this->neptune->getModule($moduleName);
            }, $input->getOption('modules'));
        }

        if ($input->getOption('exclude-modules')) {
            $excludedModules = $input->getOption('exclude-modules');

            return array_filter($this->neptune->getModules(), function ($module) use ($excludedModules) {
                return !in_array($module->getName(), $excludedModules);
            });
        }

        return $this->neptune->getModules();
    }
}

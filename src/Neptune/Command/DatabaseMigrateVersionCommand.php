<?php

namespace Neptune\Command;

use Neptune\Console\Console;
use Neptune\Console\ConsoleLogger;
use Neptune\Database\Migration\MigrationRunner;
use Neptune\Service\AbstractModule;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * DatabaseMigrateVersionCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DatabaseMigrateVersionCommand extends DatabaseMigrateListCommand
{

    protected $name = 'database:migrate:version';
    protected $description = 'Migrate to a specific database schema version';

    protected function configure()
    {
        parent::configure();
        $this->addArgument(
                 'version',
                 InputArgument::OPTIONAL,
                 'The version to migrate to.'
             )
             ->addOption(
                 'module',
                 'm',
                 InputOption::VALUE_REQUIRED,
                 'The module containing the migration version.',
                 $this->getDefaultModule()
             );
    }

    public function go(Console $console)
    {
        $runner = new MigrationRunner($this->neptune['db'], new ConsoleLogger($this->output));

        $module = $this->neptune->getModule($this->input->getOption('module'));

        $version = $this->input->getArgument('version');

        if (!$version && $version !== '0') {
            //no version has been specified, so prompt for a version
            //by displaying all available migrations, highlighting the
            //current version.

            $migrations = $this->getMigrationsWithHighlight($runner, $module);

            $messages = array_values($migrations);
            $versions = array_keys($migrations);

            $prompt =  sprintf('Select a migration for module <info>%s</info>:', $module->getName());
            $dialog = $this->getHelper('dialog');
            $index = $dialog->select($this->output, $prompt, $messages);
            $version = $versions[$index];
        }

        $current = $runner->getCurrentVersion($module);
        if ((int) $version === (int) $current) {
            $this->output->writeln("Database is already at version $version");

            return true;
        }

        $runner->migrate($module, $version);
    }

}

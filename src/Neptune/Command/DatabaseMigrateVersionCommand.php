<?php

namespace Neptune\Command;

use Neptune\Console\ConsoleLogger;
use Neptune\Database\Migration\MigrationRunner;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
             )
             ->addOption(
                 'force',
                 'f',
                 InputOption::VALUE_NONE,
                 'Log each migration as successful even if it fails.'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $runner = new MigrationRunner($this->neptune['db'], new ConsoleLogger($output));

        if ($input->getOption('force')) {
            $runner->ignoreExceptions();
        }

        $module = $this->neptune->getModule($input->getOption('module'));

        $version = $input->getArgument('version');

        if (!$version && $version !== '0') {
            //no version has been specified, so prompt for a version
            //by displaying all available migrations, highlighting the
            //current version.

            $migrations = $this->getMigrationsWithHighlight($runner, $module);

            $messages = array_values($migrations);
            $versions = array_keys($migrations);

            $prompt =  sprintf('Select a migration for module <info>%s</info>:', $module->getName());
            $dialog = $this->getHelper('dialog');
            $index = $dialog->select($output, $prompt, $messages);
            $version = $versions[$index];
        }

        $current = $runner->getCurrentVersion($module);
        if ((int) $version === (int) $current) {
            $output->writeln("Database is already at version $version");

            return true;
        }

        $runner->migrate($module, $version);
    }

}

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
class DatabaseMigrateVersionCommand extends Command
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

        $runner->migrate($module, $version);
    }

    /**
     * Get a list of all available migrations in a module, most recent
     * first, highlighting the current version. Each list entry
     * contains the version number and the migration description.
     */
    protected function getMigrationsWithHighlight(MigrationRunner $runner, AbstractModule $module)
    {
        $available = $runner->getAllMigrations($module);

        $current_version = $runner->getCurrentVersion($module);

        $migrations = array_map(function ($migration) use ($current_version) {
            if ($migration->getVersion() === $current_version) {
                return sprintf('<info>%s : %s</info>', $migration->getVersion(), $migration->getDescription());
            }

            return sprintf('%s : %s', $migration->getVersion(), $migration->getDescription());
        }, $available);

        $zero =  '             0 : Revert all migrations';
        if ($current_version === 0) {
            $zero = '<info>' . $zero . '</info>';
        }

        $migrations = [$zero] + $migrations;

        return $migrations;
    }

}

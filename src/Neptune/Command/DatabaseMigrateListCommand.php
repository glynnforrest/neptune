<?php

namespace Neptune\Command;

use Neptune\Database\Migration\MigrationRunner;
use Neptune\Service\AbstractModule;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * DatabaseMigrateListCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DatabaseMigrateListCommand extends Command
{

    protected $name = 'database:migrate:list';
    protected $description = 'List all migrations in a module';

    protected function configure()
    {
        parent::configure();
        $this->addArgument(
                 'module',
                 InputArgument::OPTIONAL,
                 'The module containing the migrations'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $runner = new MigrationRunner($this->neptune['db']);

        $module = $this->getModuleArgument($input, $output);
        $output->writeln(sprintf('Migrations for module <info>%s</info>:', $module->getName()));

        foreach ($this->getMigrationsWithHighlight($runner, $module) as $message) {
            $output->writeln($message);
        }
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
                return sprintf('<info>%s %s</info>', $migration->getVersion(), $migration->getDescription());
            }

            return sprintf('%s %s', $migration->getVersion(), $migration->getDescription());
        }, $available);

        $zero =  '             0 Revert all migrations';
        if ($current_version === 0) {
            $zero = '<info>' . $zero . '</info>';
        }

        $migrations = [$zero] + $migrations;

        return $migrations;
    }

}

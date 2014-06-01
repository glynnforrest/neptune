<?php

namespace Neptune\Command;

use Neptune\Console\Console;
use Neptune\Console\ConsoleLogger;
use Neptune\Database\Migration\MigrationRunner;

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
                 InputArgument::REQUIRED,
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
        $version = $this->input->getArgument('version');
        $module = $this->input->getOption('module');
        $runner->migrate($this->neptune->getModule($module), $version);
    }

}

<?php

namespace Neptune\Command;

use Neptune\Console\Console;
use Neptune\Console\ConsoleLogger;
use Neptune\Database\Migration\MigrationRunner;

use Symfony\Component\Console\Input\InputOption;

/**
 * DatabaseMigrateLatestCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DatabaseMigrateLatestCommand extends Command
{

    protected $name = 'database:migrate:latest';
    protected $description = 'Migrate to the latest version of the database schema';

    protected function configure()
    {
        parent::configure();
        $this->addOption(
                 'module',
                 'm',
                 InputOption::VALUE_REQUIRED,
                 'The module containing the migrations',
                 $this->getDefaultModule()
             );
    }

    public function go(Console $console)
    {
        $runner = new MigrationRunner($this->neptune['db'], new ConsoleLogger($this->output));
        $module = $this->input->getOption('module');
        $path = $this->getModuleDirectory($module) . 'Migrations/';
        $namespace = $this->getModuleNamespace($module) . '\\Migrations\\';
        $runner->migrateLatest($path, $namespace);
    }

}

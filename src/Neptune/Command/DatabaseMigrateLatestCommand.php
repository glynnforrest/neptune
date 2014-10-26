<?php

namespace Neptune\Command;

use Neptune\Console\ConsoleLogger;
use Neptune\Database\Migration\MigrationRunner;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $runner = new MigrationRunner($this->neptune['db'], new ConsoleLogger($output));
        $module = $input->getOption('module');
        $runner->migrateLatest($this->neptune->getModule($module));
    }

}

<?php

namespace Neptune\Command;

use Neptune\Console\Console;
use Neptune\Console\ConsoleLogger;
use Neptune\Database\Migration\MigrationRunner;
use Neptune\Database\Migration\Exception\MigrationNotFoundException;

use Symfony\Component\Console\Input\InputOption;

/**
 * DatabaseMigrateAllCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DatabaseMigrateAllCommand extends DatabaseMigrateListCommand
{

    protected $name = 'database:migrate:all';
    protected $description = 'Migrate all modules to the latest database version';

    protected function configure()
    {
        parent::configure();
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Log each migration as successful even if it fails.'
        );
    }

    public function go(Console $console)
    {
        $runner = new MigrationRunner($this->neptune['db'], new ConsoleLogger($this->output));

        if ($this->input->getOption('force')) {
            $runner->ignoreExceptions();
        }

        foreach ($this->neptune->getModules() as $module) {
            try {
                $runner->migrateLatest($module);
                $this->output->writeln(sprintf('Module <info>%s</info> up to date', $module->getName()));
            } catch (MigrationNotFoundException $e) {
                //this means the module doesn't have a migrations
                //folder, so skip it
                continue;
            }
        }
    }

}

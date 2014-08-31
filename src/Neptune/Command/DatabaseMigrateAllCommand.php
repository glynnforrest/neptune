<?php

namespace Neptune\Command;

use Neptune\Console\Console;
use Neptune\Console\ConsoleLogger;
use Neptune\Database\Migration\MigrationRunner;

/**
 * DatabaseMigrateAllCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DatabaseMigrateAllCommand extends DatabaseMigrateListCommand
{

    protected $name = 'database:migrate:all';
    protected $description = 'Migrate all modules to the latest database version';

    public function go(Console $console)
    {
        $runner = new MigrationRunner($this->neptune['db'], new ConsoleLogger($this->output));

        foreach ($this->neptune->getModules() as $module) {
            try {
                $runner->migrateLatest($module);
            } catch (\Exception $e) {
                continue;
            }
        }
    }

}

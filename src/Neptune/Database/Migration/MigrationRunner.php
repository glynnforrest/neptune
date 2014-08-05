<?php

namespace Neptune\Database\Migration;

use Doctrine\DBAL\Connection;
use Neptune\Service\AbstractModule;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * MigrationRunner
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class MigrationRunner
{

    protected $connection;
    protected $logger;
    protected $log_level;
    protected $migrations_table = '_neptune_migrations';

    protected $dir;
    protected $namespace;
    protected $module_name;

    public function __construct(Connection $connection, LoggerInterface $logger = null, $log_level = LogLevel::INFO)
    {
        $this->connection = $connection;
        $this->logger = $logger;
        $this->log_level = $log_level;
    }

    public function initMigrationsTable()
    {
        $sm = $this->connection->getSchemaManager();

        foreach ($sm->listTables() as $table) {
            if ($table->getName() === $this->migrations_table) {
                return true;
            }
        }

        $current = $sm->createSchema();
        $new = clone $current;
        $table = $new->createTable('_neptune_migrations');
        $table->addColumn("version", "string", array("length" => "14"));
        $table->addColumn("module", "string", array("length" => "255"));

        $queries = $current->getMigrateToSQL($new, $this->connection->getDatabasePlatform());
        foreach ($queries as $query) {
            $this->connection->executeQuery($query);
        }
    }

    public function migrate(AbstractModule $module, $version)
    {
        $this->initMigrationsTable();
        $migrations_directory = $module->getDirectory() . 'Migrations/';
        if (!is_dir($migrations_directory)) {
            throw new \Exception($migrations_directory . ' does not exist');
        }

        $this->dir = $migrations_directory;
        $this->namespace = $module->getNamespace() . '\\Migrations\\';
        $this->module_name = $module->getName();

        $file = $migrations_directory . 'Migration' . $version . '.php';
        if (!file_exists($file) && (int) $version !== 0) {
            throw new \Exception("Migration not found: $file");
        }

        $current = $this->getCurrentVersion();
        if ($version == $current) {
            $this->log("Database is already at version $version");

            return true;
        }

        //begin transaction
        if ($current < $version) {
            $this->migrateUp($current, $version);
        } else {
            $this->migrateDown($current, $version);
        }
        //end transaction
    }

    /**
     * Get the Migrations between a lower and higher version. The
     * lower version is not included but the higher is.
     */
    protected function getMigrationsBetween($lower_version, $higher_version)
    {
        $migrations = [];
        $files = new \DirectoryIterator($this->dir);
        foreach ($files as $file) {
            if ($file->isDot() || !$this->isValidFile($file)) {
                continue;
            }
            $class = $this->namespace . $file->getBasename('.php');
            $r = new \ReflectionClass($class);
            if (!$r->isSubclassOf('Neptune\\Database\\Migration\\AbstractMigration') || $r->isAbstract()) {
                continue;
            }
            $migration = $r->newInstance();
            $version = $migration->getVersion();
            if ($version <= $lower_version || $version > $higher_version) {
                unset($migration);
                continue;
            }
            $migrations[$version] = $migration;
        }

        return $migrations;
    }

    public function migrateLatest(AbstractModule $module)
    {
        $migrations_directory = $module->getDirectory() . 'Migrations/';
        if (!is_dir($migrations_directory)) {
            throw new \Exception($migrations_directory . ' does not exist');
        }
        $files = scandir($migrations_directory, 1);
        $version = substr($files[0], -18, -4);

        return $this->migrate($module, $version);
    }

    protected function isValidFile($file)
    {
        $filename = $file->getFilename();
        //Possible migrations are files of the form MigrationYYYYMMDDHHMMSS.php
        if ($file->isFile() || preg_match('`Migration\d{14}.php`', $filename)) {
            return true;
        }

        return false;
    }

    /**
     * @param AbstractMigration $migration
     * @param bool              $direction True for up, false for down
     */
    protected function runMigration(AbstractMigration $migration, $direction)
    {
        $current = $this->connection->getSchemaManager()->createSchema();
        $new = clone $current;

        if ($direction) {
            $migration->up($new);
        } else {
            $migration->down($new);
        }

        $queries = $current->getMigrateToSQL($new, $this->connection->getDatabasePlatform());
        foreach ($queries as $query) {
            $this->connection->executeQuery($query);
        }
    }

    protected function migrateUp($current, $version)
    {
        //get all migrations between the current and the version
        $migrations = $this->getMigrationsBetween($current, $version);
        //sort the migrations by version number, lowest first
        sort($migrations);

        foreach ($migrations as $migration) {
            $this->log('Executing ' . get_class($migration));
            $this->runMigration($migration, true);

            $this->connection->insert($this->migrations_table, [
                'version' => $migration->getVersion(),
                'module' => $this->module_name
            ]);
        }
    }

    protected function migrateDown($current, $version)
    {
        //get all migrations between the version and the current
        $migrations = $this->getMigrationsBetween($version, $current);
        //sort the migrations by version number, highest first
        rsort($migrations);

        foreach ($migrations as $migration) {
            $this->log('Reverting ' . get_class($migration));
            $this->runMigration($migration, false);

            $this->connection->delete($this->migrations_table, [
                'version' => $migration->getVersion(),
                'module' => $this->module_name
            ]);
        }
    }

    protected function getCurrentVersion()
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('version')
           ->from($this->migrations_table)
           ->where('module = ?')
           ->orderBy('version', 'DESC');
        $stmt = $this->connection->prepare($qb->getSql());
        $stmt->execute([$this->module_name]);
        $result = $stmt->fetchColumn();

        return $result ? $result : 0;
    }

    protected function log($message)
    {
        if ($this->logger) {
            $this->logger->log($this->log_level, $message);
        }
    }

}

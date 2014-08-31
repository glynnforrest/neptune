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

        $migrations = $this->getAllMigrations($module);
        if (!isset($migrations[$version]) && (int) $version !== 0) {
            throw new \Exception("Migration version not found: $version");
        }

        $current = $this->getCurrentVersion($module);
        if ((int) $version === (int) $current) {
            return true;
        }

        //begin transaction
        if ($current < $version) {
            $this->migrateUp($module, $current, $version);
        } else {
            $this->migrateDown($module, $current, $version);
        }
        //end transaction
    }

    /**
     * Get migrations between a lower and higher version in a module. The
     * lower version is not included but the higher is.
     */
    protected function getMigrationsBetween(AbstractModule $module, $lower_version, $higher_version)
    {
        $migrations = $this->getAllMigrations($module);

        return array_filter($migrations, function ($migration) use ($lower_version, $higher_version) {
            $version = (int) $migration->getVersion();

            return $version > (int) $lower_version && $version <= (int) $higher_version;
        });
    }

    protected function getMigrationsDirectory(AbstractModule $module)
    {
        $dir = $module->getDirectory() . 'Migrations/';
        if (!is_dir($dir)) {
            throw new \Exception($dir . ' does not exist');
        }

        return $dir;
    }

    /**
     * Get all migrations from a module.
     */
    public function getAllMigrations(AbstractModule $module)
    {
        $namespace = $module->getNamespace() . '\\Migrations\\';
        $directory = $this->getMigrationsDirectory($module);

        $migrations = [];
        $files = new \DirectoryIterator($directory);
        foreach ($files as $file) {
            //Possible migrations are files of the form MigrationYYYYMMDDHHMMSS.php
            if (!$file->isFile() || !preg_match('`Migration\d{14}\w*.php`', $file->getFilename())) {
                continue;
            }
            $class = $namespace . $file->getBasename('.php');
            $r = new \ReflectionClass($class);
            if (!$r->isSubclassOf('Neptune\\Database\\Migration\\AbstractMigration') || $r->isAbstract()) {
                continue;
            }
            $migration = $r->newInstance();
            $migrations[$migration->getVersion()] = $migration;
        }

        return $migrations;
    }

    public function migrateLatest(AbstractModule $module)
    {
        $migrations = $this->getAllMigrations($module);
        $version = array_pop($migrations)->getVersion();

        return $this->migrate($module, $version);
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

    protected function migrateUp(AbstractModule $module, $current, $version)
    {
        //get all migrations between the current and the version
        $migrations = $this->getMigrationsBetween($module, $current, $version);
        //sort the migrations by version number, lowest first
        ksort($migrations);

        foreach ($migrations as $migration) {
            $this->log(sprintf('Executing %s %s', $migration->getVersion(), $migration->getDescription()));
            $this->runMigration($migration, true);

            $this->connection->insert($this->migrations_table, [
                'version' => $migration->getVersion(),
                'module' => $module->getName()
            ]);
        }
    }

    protected function migrateDown(AbstractModule $module, $current, $version)
    {
        //get all migrations between the version and the current
        $migrations = $this->getMigrationsBetween($module, $version, $current);
        //sort the migrations by version number, highest first
        krsort($migrations);

        foreach ($migrations as $migration) {
            $this->log(sprintf('Reverting %s %s', $migration->getVersion(), $migration->getDescription()));
            $this->runMigration($migration, false);

            $this->connection->delete($this->migrations_table, [
                'version' => $migration->getVersion(),
                'module' => $module->getName()
            ]);
        }
    }

    public function getCurrentVersion(AbstractModule $module)
    {
        $this->initMigrationsTable();

        $qb = $this->connection->createQueryBuilder();
        $qb->select('version')
           ->from($this->migrations_table)
           ->where('module = ?')
           ->orderBy('version', 'DESC');
        $stmt = $this->connection->prepare($qb->getSql());
        $stmt->execute([$module->getName()]);
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

<?php

namespace Neptune\Database\Migration;

use Neptune\Database\Driver\DatabaseDriverInterface;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * MigrationRunner
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class MigrationRunner
{

    protected $driver;
    protected $namespace;
    protected $logger;
    protected $log_level;

    public function __construct(DatabaseDriverInterface $driver, LoggerInterface $logger = null, $log_level = LogLevel::INFO)
    {
        $this->driver = $driver;
        $this->logger = $logger;
        $this->log_level = $log_level;
    }

    public function createTable()
    {
        //create sql for migrations
    }

    public function migrate($migrations_directory, $namespace, $version)
    {
        if(!is_dir($migrations_directory)) {
            throw new \Exception($migrations_directory . ' does not exist');
        }
        $this->dir = $migrations_directory;
        $this->namespace = $namespace;

        $file = $migrations_directory . 'Migration' . $version . '.php';
        if (!file_exists($file) && (int) $version !== 0) {
            throw new \Exception("Migration not found: $file");
        }

        $this->messages = array();

        //begin transaction
        $current = $this->getCurrentMigration();
        if ($version == $current) {
            $this->log("Database is already at version $version");
            return true;
        }
        $direction = ($current < $version) ? true : false;
        //true is up, false is down
        $migrations = $this->getMigrations($current, $version, $direction);
        foreach ($migrations as $migration) {
            $this->executeMigration($migration, $direction);
        }
        //end transaction
    }

    public function migrateLatest($migrations_directory, $namespace)
    {
        if(!is_dir($migrations_directory)) {
            throw new \Exception($migrations_directory . ' does not exist');
        }
        $files = scandir($migrations_directory, 1);
        $version = substr($files[0], -18, -4);

        return $this->migrate($migrations_directory, $namespace, $version);
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

    protected function getMigrations($current, $version, $up = true)
    {
        $migrations = array();
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
            //check for going up
            if ($up && !$migration->isRequiredUp($current, $version)) {
                unset($migration);
                continue;
            }

            //check for going down
            if (!$up && !$migration->isRequiredDown($current, $version)) {
                unset($migration);
                continue;
            }
            $migrations[] = $migration;
        }
        return $migrations;
    }

    public function executeMigration(AbstractMigration $migration, $up = true)
    {
        $up ? $migration->up() : $migration->down();
        $sql = $migration->getSql();
        $this->log("Executing " . get_class($migration));
        foreach ($sql as $query) {
            $stmt = $this->driver->prepare($query);
            try {
                $stmt->execute();
            } catch (\Exception $e) {
                $this->log($e->getMessage());
            }
        }
        if ($up) {
            $this->logVersionUp($migration->getVersion());
        } else {
            $this->logVersionDown($migration->getVersion());
        }
    }

    protected function getCurrentMigration()
    {
        $sql = 'SELECT version from neptune_migrations ORDER BY version DESC';
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetch();
        if ($res) {
            return $res['version'];
        }
        return 0;
    }

    protected function logVersionUp($version)
    {
        $sql = sprintf('INSERT INTO neptune_migrations values ("%s")', $version);
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
    }

    protected function logVersionDown($version)
    {
        $sql = sprintf('DELETE FROM neptune_migrations WHERE version = "%s"', $version);
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
    }

    protected function log($message)
    {
        if ($this->logger) {
            $this->logger->log($this->log_level, $message);
        }
    }

}

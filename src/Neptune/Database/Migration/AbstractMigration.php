<?php

namespace Neptune\Database\Migration;

use Neptune\Core\Neptune;
use Neptune\Core\NeptuneAwareInterface;

/**
 * AbstractMigration
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class AbstractMigration implements NeptuneAwareInterface
{

    protected $description;
    protected $sql = array();
    protected $neptune;

    public function setNeptune(Neptune $neptune)
    {
        $this->neptune = $neptune;
    }

    public function getNeptune()
    {
        return $this->neptune;
    }

    abstract public function up();

    abstract public function down();

    protected function addSql($sql)
    {
        $this->sql[] = $sql;
    }

    public function getSql()
    {
        return $this->sql;
    }

    public function getVersion()
    {
        return substr(get_class($this), -14);
    }

    public function isRequiredUp($current, $version)
    {
        $migration_version = $this->getVersion();
        if ($migration_version < $current) {
            return false;
        }
        if ($migration_version > $version) {
            return false;
        }
        return true;
    }

    public function isRequiredDown($current, $version)
    {
        $migration_version = $this->getVersion();
        if ($migration_version > $current) {
            return false;
        }
        if ($migration_version < $version) {
            return false;
        }
        return true;
    }

    public function getDescription()
    {
        return $this->description;
    }

}

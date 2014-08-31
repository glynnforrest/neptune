<?php

namespace Neptune\Database\Migration;

use Neptune\Core\Neptune;
use Neptune\Core\NeptuneAwareInterface;
use Doctrine\DBAL\Schema\Schema;

/**
 * AbstractMigration
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class AbstractMigration implements NeptuneAwareInterface
{

    protected $description;
    protected $neptune;

    public function setNeptune(Neptune $neptune)
    {
        $this->neptune = $neptune;
    }

    public function getNeptune()
    {
        return $this->neptune;
    }

    abstract public function up(Schema $schema);

    abstract public function down(Schema $schema);

    public function getVersion()
    {
        $pieces = explode('\\', get_class($this));

        /* ----9---- | -----14------- */
        /* Migration | YYYYMMDDHHMMSS | NameOfThisMigration */
        return substr(array_pop($pieces), 9, 14);
    }

    public function getDescription()
    {
        return $this->description;
    }

}

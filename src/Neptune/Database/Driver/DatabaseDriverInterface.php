<?php

namespace Neptune\Database\Driver;

/**
 * DatabaseDriverInterface
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 */
interface DatabaseDriverInterface
{
    /**
     * @return \PDOStatement
     */
    public function prepare($query);

    public function quote($string);

    public function lastInsertId($column = null);

    /**
     * @return AbstractQuery
     */
    public function select();

    /**
     * @return AbstractQuery
     */
    public function insert();

    /**
     * @return AbstractQuery
     */
    public function update();

    /**
     * @return AbstractQuery
     */
    public function delete();

    /**
     * @return RelationManager
     */
    public function getRelationManager();

}

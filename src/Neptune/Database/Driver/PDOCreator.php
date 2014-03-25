<?php

namespace Neptune\Database\Driver;

/**
 * PDOCreator is a simple wrapper to create a PDO instance.  Mocking
 * this class allows for thorough testing of DatabaseFactory.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class PDOCreator
{

    public function createPDO($dsn, $user, $pass, array $options = array())
    {
        return new \PDO($dsn, $user, $pass, $options);
    }

}

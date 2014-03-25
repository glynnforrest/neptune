<?php

namespace Neptune\Database;

use Symfony\Component\EventDispatcher\Event;

/**
 * DatabaseEvent
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DatabaseEvent extends Event
{

    const QUERY = 'database.query';
    const PREPARE = 'database.prepare';
    const EXECUTE = 'database.execute';
    const QUOTE = 'database.quote';

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

}

<?php

namespace Neptune\Database\Driver;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Neptune\Database\DatabaseEvent;

/**
 * DebugDriver
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class DebugDriver implements DatabaseDriverInterface
{

    protected $dispatcher;
    protected $driver;

    public function __construct(DatabaseDriverInterface $driver, EventDispatcherInterface $dispatcher)
    {
        $this->driver = $driver;
        $this->dispatcher = $dispatcher;
    }

    protected function sendEvent($event_name, $data)
    {
        if ($this->dispatcher->hasListeners($event_name)) {
            $event = new DatabaseEvent($data);
            $this->dispatcher->dispatch($event_name, $event);
        }
    }

    public function prepare($query)
    {
        $this->sendEvent(DatabaseEvent::PREPARE, $query);
        return $this->driver->prepare($query);
    }

    public function quote($string)
    {
        //send event
        return $this->driver->quote($string);
    }

    public function lastInsertId($column = null)
    {
        return $this->driver->lastInsertId();
    }

    public function select()
    {
        return $this->driver->select();
    }

    public function insert()
    {
        return $this->driver->insert();
    }

    public function update()
    {
        return $this->driver->update();
    }

    public function delete()
    {
        return $this->driver->delete();
    }

    public function getRelationManager()
    {
        return $this->driver->getRelationManager();
    }

}

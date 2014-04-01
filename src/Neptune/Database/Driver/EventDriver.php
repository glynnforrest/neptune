<?php

namespace Neptune\Database\Driver;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Neptune\Database\DatabaseEvent;

/**
 * EventDriver
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class EventDriver implements DatabaseDriverInterface
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
        return $this->driver->select()->setDatabase($this);
    }

    public function insert()
    {
        return $this->driver->insert()->setDatabase($this);
    }

    public function update()
    {
        return $this->driver->update()->setDatabase($this);
    }

    public function delete()
    {
        return $this->driver->delete()->setDatabase($this);
    }

    public function getRelationManager()
    {
        return $this->driver->getRelationManager();
    }

    public function getPDO()
    {
        return $this->driver->getPDO();
    }

}

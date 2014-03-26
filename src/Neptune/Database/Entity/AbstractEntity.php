<?php

namespace Neptune\Database\Entity;

/**
 * AbstractEntity
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class AbstractEntity
{

    protected $values = array();
    protected $modified = array();
    protected $stored;

    /**
     * Convenience wrapper to get().
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Get the value of $key. If the method get<Key> exists, the return
     * value will be the output of calling this function.
     *
     * @param string $key The name of the key to get.
     */
    public function get($key)
    {
        $method = 'get' . ucfirst($key);
        if (method_exists($this, $method) && $method !== 'get') {
            return $this->$method();
        }

        return $this->getRaw($key);
    }

    /**
     * Get the value of $key. If $key doesn't exist, null will be returned.
     *
     * @param string $key The name of the key to get.
     */
    abstract public function getRaw($key);

    /**
     * Convenience wrapper to set().
     */
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Set $key to $value. If the method set<Key> exists, $value will
     * be the output of calling this function with $value as an
     * argument.
     *
     * @param string $key   The name of the key to set.
     * @param mixed  $value The value to set. This may a related object.
     */
    public function set($key, $value)
    {
        $method = 'set' . ucfirst($key);
        if (method_exists($this, $method)) {
            $value = $this->$method($value);
        }
        $this->setRaw($key, $value);
        //MOVE TO THING
        //if the key we are setting is a key for a relation, tell that
        //relation to update
        if (isset($this->relation_keys[$key])) {
            $this->relation_objects[$this->relation_keys[$key]]->updateKey(get_class($this));
        }
    }

    /**
     * Set $key to $value, ignoring any set<Key> methods.
     *
     * @param string $key   The name of the key to set.
     * @param mixed  $value The value to set.
     */
    abstract public function setRaw($key, $value);

    public function has($name)
    {
        return $this->get($name) ? true : false;
    }

    public function setValues($values = array())
    {
        foreach ($values as $k => $v) {
            $this->set($k, $v);
        }

        return $this;
    }

    public function setValuesRaw($values = array())
    {
        foreach ($values as $k => $v) {
            $this->setRaw($k, $v);
        }

        return $this;
    }

    public function __isset($key)
    {
        return isset($this->values[$key]);
    }

    public function getValues()
    {
        $return = array();
        foreach ($this->values as $k => $v) {
            $return[$k] = $this->get($k);
        }

        return $return;
    }

    public function getValuesRaw()
    {
        return $this->values;
    }

    public function setStored()
    {
        $this->stored = true;
    }

    public function setNew()
    {
        $this->stored = false;
    }

}

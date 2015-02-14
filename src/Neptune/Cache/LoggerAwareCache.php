<?php

namespace Neptune\Cache;

use Doctrine\Common\Cache\Cache;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * LoggerAwareCache
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class LoggerAwareCache implements Cache, LoggerAwareInterface
{
    protected $cache;
    protected $logger;

    public function __construct(Cache $cache, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function fetch($id)
    {
        if ($result = $this->cache->fetch($id)) {
            $this->logger->debug("CACHE HIT: $id");

            return $result;
        }

        $this->logger->debug("CACHE MISS: $id");

        return $result;
    }

    public function contains($id)
    {
        if ($result = $this->cache->contains($id)) {
            $this->logger->debug("CACHE HIT: $id");

            return $result;
        }
        $this->logger->debug("CACHE MISS: $id");

        return $result;
    }

    public function save($id, $data, $lifeTime = 0)
    {
        $this->logger->debug(sprintf('CACHE SAVE: %s for %d seconds', $id, $lifeTime));

        return $this->cache->save($id, $data, $lifeTime);
    }

    public function delete($id)
    {
        $this->logger->debug("CACHE DELETE: $id");

        return $this->cache->delete($id);
    }

    public function getStats()
    {
        return $this->cache->getStats();
    }
}

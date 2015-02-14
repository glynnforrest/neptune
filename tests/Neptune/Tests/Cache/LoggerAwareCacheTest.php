<?php

namespace Neptune\Tests\Cache;

use Neptune\Cache\LoggerAwareCache;

/**
 * LoggerAwareCacheTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class LoggerAwareCacheTest extends \PHPUnit_Framework_TestCase
{
    protected $inner_cache;
    protected $logger;
    protected $cache;

    public function setUp()
    {
        $this->inner_cache = $this->getMock('Doctrine\Common\Cache\Cache');
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->cache = new LoggerAwareCache($this->inner_cache, $this->logger);
    }

    public function testLoggerAware()
    {
        $this->assertInstanceOf('Psr\Log\LoggerAwareInterface', $this->cache);
    }
}

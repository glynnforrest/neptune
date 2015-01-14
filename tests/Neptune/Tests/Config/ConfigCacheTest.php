<?php

namespace Neptune\Tests\Config;

use Temping\Temping;
use Neptune\Config\ConfigCache;
use Neptune\Config\Config;

/**
 * ConfigCacheTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfigCacheTest extends \PHPUnit_Framework_TestCase
{
    protected $temp;
    protected $cache_file;
    protected $cache;

    public function setUp()
    {
        $this->temp = new Temping();
        $this->cache_file = 'storage/cache/config-test.php';
        $this->cache = new ConfigCache($this->temp->getPathname($this->cache_file));
    }

    public function tearDown()
    {
        $this->temp->reset();
    }

    /**
     * Strip out whitespace and new lines from config settings to make
     * them easier to compare against.
     */
    protected function removeWhitespace($content)
    {
        return preg_replace('`\s+`', '', $content);
    }

    public function testIsFreshNoFile()
    {
        $this->assertFalse($this->cache->isSaved());
    }

    /**
     * Config cache only checks the file exists for the sake of speed.
     */
    public function testIsFreshWithFile()
    {
        $this->temp->create($this->cache_file);
        $this->assertTrue($this->cache->isSaved());
    }

    public function testGetConfig()
    {
        $this->temp->create($this->cache_file, file_get_contents(__DIR__.'/fixtures/config.php'));
        $config = $this->cache->getConfig();
        $this->assertInstanceOf('Neptune\Config\Config', $config);
        $this->assertSame('bar', $config->get('foo'));
    }

    public function testGetConfigInvalidCacheFile()
    {
        $this->temp->create($this->cache_file);
        $msg = sprintf('Configuration cache for "%s" is invalid.', $this->temp->getPathname($this->cache_file));
        $this->setExpectedException('Neptune\Exceptions\ConfigFileException', $msg);
        $this->cache->getConfig();
    }

    public function testSave()
    {
        $config = new Config();
        $config->set('foo', 'bar');
        $this->temp->init();
        $this->assertFalse($this->temp->exists('cache.php'), 'Cache file must not exist.');

        $cache = new ConfigCache($this->temp->getPathname('cache.php'));
        $cache->save($config);
        $this->assertTrue($this->temp->exists('cache.php'), 'Cache file must exist.');
    }

    public function testSaveCreatesDirectories()
    {
        $config = new Config();
        $config->set('foo', 'bar');
        $this->temp->init();
        $this->assertFalse($this->temp->exists($this->cache_file), 'Cache file must not exist.');

        $this->cache->save($config);
        $this->assertTrue($this->temp->exists($this->cache_file), 'Cache file must exist.');
    }

    public function testLoadSavedConfig()
    {
        $config = new Config();
        $config->set('foo', 'bar');
        $this->cache->save($config);

        $cached_config = $this->cache->getConfig();
        $this->assertInstanceOf('Neptune\Config\Config', $config);
        $this->assertSame('bar', $config->get('foo'));
    }
}

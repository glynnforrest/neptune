<?php

namespace Neptune\Tests\Config;

use Neptune\Config\NeptuneConfig;
use Neptune\Config\Config;

use Temping\Temping;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * NeptuneConfigTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class NeptuneConfigTest extends \PHPUnit_Framework_TestCase
{

    protected $temp;

    public function setUp()
    {
        $this->temp = new Temping();
    }

    public function tearDown()
    {
        $this->temp->reset();
    }

    public function testConstructNoFile()
    {
        $config = new NeptuneConfig('/root', false);
        $this->assertInstanceOf('Neptune\Config\Config', $config);
        $this->assertNull($config->getFilename());
    }

    public function testConstructWithFile()
    {
        $config = new Config('foo');
        $config->set('foo', 'bar');
        $this->temp->create('config/neptune.php', $config->toString());
        $neptune_config = new NeptuneConfig($this->temp->getDirectory());
        $this->assertInstanceOf('Neptune\Config\Config', $neptune_config);
        $this->assertSame('bar', $neptune_config->get('foo'));
    }

    public function testConstructWithFileGiven()
    {
        $config = new NeptuneConfig($this->temp->getDirectory(), __DIR__ . '/fixtures/config.php');
        $this->assertSame('bar', $config->get('foo'));
        $this->assertInstanceOf('Neptune\Config\Config', $config);
    }

    public function testRootDirectoryIsSet()
    {
        $config = new NeptuneConfig('/path/to/app/', __DIR__ . '/fixtures/config.php');
        $this->assertSame('/path/to/app/', $config->getRootDirectory());
    }

    public function testRootDirectoryIsSetWithTrailingSlash()
    {
        $config = new NeptuneConfig('/path/to/app', __DIR__ . '/fixtures/config.php');
        $this->assertSame('/path/to/app/', $config->getRootDirectory());
    }

}

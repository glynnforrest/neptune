<?php

namespace Neptune\Tests\Config\Loader;

use Neptune\Config\Loader\YamlLoader;

/**
 * YamlLoaderTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class YamlLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->loader = new YamlLoader();
    }

    public function supportsProvider()
    {
        return [
            ['foo.yml'],
            ['config/neptune.yml'],
            ['config.php', false],
            ['config', false],
            ['configyml', false],
        ];
    }

    /**
     * @dataProvider supportsProvider
     */
    public function testSupports($filename, $expected = true)
    {
        $this->assertSame($expected, $this->loader->supports($filename));
    }

    public function testLoad()
    {
        $expected = [
            'foo' => 'bar',
            'one' => [
                'one' => 'one.one',
                'two' => 'one.two',
            ],
        ];
        $this->assertSame($expected, $this->loader->load(__DIR__.'/../fixtures/config.yml'));
    }
}

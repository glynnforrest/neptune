<?php

namespace Neptune\Tests\Console;

use Neptune\Console\Application;
use Neptune\Core\Neptune;
use Neptune\Config\Config;

/**
 * ApplicationTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    protected $app;
    protected $neptune;

    public function setUp()
    {
        $this->neptune = new Neptune('some/dir');
        $this->neptune['config'] = new Config();

        $this->app = new Application($this->neptune);
    }

    /**
     * Check that all neptune commands are registered without errors.
     */
    public function testRegisterNeptuneCommands()
    {
        $this->app->registerNamespace('Neptune\\Command', __DIR__ . '/../../../../src/Neptune/Command/');
    }
}

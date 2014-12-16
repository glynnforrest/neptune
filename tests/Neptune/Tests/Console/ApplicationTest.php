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
        $this->neptune['config'] = new Config('testing');

        //needed for now as create commands get the default module in arguments
        $module = $this->getMock('Neptune\Service\AbstractModule');
        $this->neptune->addModule($module);

        $this->app = new Application($this->neptune);
    }

    public function testRegisterNeptuneCommands()
    {
        //check that all neptune commands are registered correctly.
        $this->app->registerNamespace('Neptune', __DIR__ . '/../../../../src/Neptune/Command/');
    }
}

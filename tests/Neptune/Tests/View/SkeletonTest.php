<?php

namespace Neptune\Tests\View;

use Neptune\View\Skeleton;
use Neptune\Core\Config;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * SkeletonTest
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SkeletonTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        $c = Config::create('neptune');
        $c->set('namespace', 'Testapp');
    }

    public function tearDown() {
        Config::load('neptune')->unload();
    }

    public function testConstruct() {
        $this->assertTrue(Skeleton::loadAbsolute('dummy') instanceof Skeleton);
    }

}

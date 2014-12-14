<?php

namespace Neptune\Tests\View;

use Neptune\View\Skeleton;

use Temping\Temping;

/**
 * SkeletonTest
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SkeletonTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAndSetNamespace()
    {
        $temp = new Temping();
        $temp->create('skeleton.php');
        $skeleton = new Skeleton($temp->getPathname('skeleton.php'));
        $this->assertSame($skeleton, $skeleton->setNamespace('Foo'));
        $this->assertSame('Foo', $skeleton->getNamespace());
        $temp->reset();
    }

}

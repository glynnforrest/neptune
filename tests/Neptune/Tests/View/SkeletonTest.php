<?php

namespace Neptune\Tests\View;

use Neptune\View\Skeleton;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * SkeletonTest
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SkeletonTest extends \PHPUnit_Framework_TestCase {

	public function testGetAndSetNamespace() {
		$skeleton = new Skeleton(null);
		$this->assertSame($skeleton, $skeleton->setNamespace('Foo'));
		$this->assertSame('Foo', $skeleton->getNamespace());
	}

}

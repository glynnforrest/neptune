<?php

namespace Neptune\Helpers;

require_once __DIR__ . '/../../bootstrap.php';

class BitmaskTest extends \PHPUnit_Framework_TestCase {

	const ONE = 1;
	const TWO = 2;
	const FOUR = 4;

	public function testGetBitmask() {
		$bitmask = new Bitmask(51);
		$this->assertEquals(51, $bitmask->getBitmask());
	}

	public function testSetBitmask() {
		$bitmask = new Bitmask();
		$bitmask->setBitmask(31);
		$this->assertEquals(31, $bitmask->getBitmask());
		$bitmask = new Bitmask(4);
		$bitmask->setBitmask(1);
		$this->assertEquals(1, $bitmask->getBitmask());
	}

	public function testHasProperty() {
		$bitmask = new Bitmask(5);
		$this->assertTrue($bitmask->hasProperty(BitmaskTest::ONE));
		$this->assertFalse($bitmask->hasProperty(BitmaskTest::TWO));
		$this->assertTrue($bitmask->hasProperty(5));
		$this->assertTrue($bitmask->hasProperty(BitmaskTest::ONE | BitmaskTest::FOUR));
		$this->assertFalse($bitmask->hasProperty(BitmaskTest::ONE | BitmaskTest::TWO));
	}

	public function testAddProperty() {
		$bitmask = new Bitmask();
		$bitmask->addProperty(2);
		$this->assertEquals(2, $bitmask->getBitmask());
		$bitmask->addProperty(1);
		$this->assertEquals(3, $bitmask->getBitmask());
		$bitmask->addProperty(4 | 1);
		$this->assertEquals(7, $bitmask->getBitmask());
	}

	public function testRemoveProperty() {
		$bitmask = new Bitmask(17);
		$bitmask->removeProperty(1);
		$this->assertFalse($bitmask->hasProperty(1));
	}

}

?>

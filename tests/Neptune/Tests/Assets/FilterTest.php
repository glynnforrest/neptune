<?php

namespace Neptune\Tests\Assets;

use Neptune\Assets\Asset;
use Neptune\Assets\Filter;
use Neptune\Tests\Assets\UpperCaseFilter;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * FilterTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class FilterTest extends \PHPUnit_Framework_TestCase {

	public function testConstruct() {
		$a = new UpperCaseFilter();
		$this->assertTrue($a instanceof Filter);
	}

	public function testFilterAsset() {
		$a = new Asset();
		$a->setContent('content');
		$f = new UpperCaseFilter();
		$f->filterAsset($a);
		$this->assertEquals('CONTENT', $a->getContent());
	}
}

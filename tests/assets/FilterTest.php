<?php

namespace neptune\assets;

use neptune\assets\Asset;
use neptune\assets\Filter;
use neptune\tests\assets\UpperCaseFilter;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

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
?>

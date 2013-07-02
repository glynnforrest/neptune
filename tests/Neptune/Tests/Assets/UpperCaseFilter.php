<?php

namespace Neptune\Tests\Assets;

use Neptune\Assets\Asset;
use Neptune\Assets\Filter;

class UpperCaseFilter implements Filter {

	public function __construct(array $options = array()) {
	}

	public function filterAsset(Asset &$a) {
		$a->setContent(strtoupper($a->getContent()));
	}

}

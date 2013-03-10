<?php

namespace neptune\tests\assets;

use neptune\assets\Asset;
use neptune\assets\Filter;

class UpperCaseFilter implements Filter {

	public function filterAsset(Asset &$a) {
		$a->setContent(strtoupper($a->getContent()));
	}

}

?>

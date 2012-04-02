<?php
namespace neptune\assets;

use neptune\assets\Asset;

/**
 * Filter
 * @author Glynn Forrest me@glynnforrest.com
 **/
interface Filter {

	public function filterAsset(Asset &$a);
}
?>

<?php
namespace Neptune\Assets;

use Neptune\Assets\Asset;

/**
 * Filter
 * @author Glynn Forrest me@glynnforrest.com
 **/
interface Filter {

	public function filterAsset(Asset &$a);

}
?>

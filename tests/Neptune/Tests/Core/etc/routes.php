<?php
return function(\Neptune\Core\Dispatcher $d) {
	$d->route(':prefix/login', 'foo');
};
?>
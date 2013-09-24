<?php
return function(\Neptune\Core\Dispatcher $d) {
	$d->globals()->controller('foo_module_controller');
	$d->route(':prefix/login')->method('foo_module_method');
};
?>
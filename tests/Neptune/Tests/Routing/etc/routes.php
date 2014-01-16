<?php
return function(\Neptune\Routing\Dispatcher $d) {
	$d->globals()->controller('foo_module_controller');
	$d->route(':prefix/login')->method('foo_module_method');
	$d->name('secret')->route(':prefix/secret')->method('secretArea');
};
?>
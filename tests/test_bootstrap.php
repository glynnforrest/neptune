<?php

namespace neptune;
use neptune\core\Loader;
/**
 * test_bootstrap
 * @author Glynn Forrest <me@glynnforrest.com>
 * This will load just the Loader for test classes to use.
 */
if(!class_exists('\\neptune\\core\\Loader')) {
	require(__DIR__ . '/../core/Loader.php');
	spl_autoload_register(__NAMESPACE__ . '\core\Loader::load');
	Loader::addNamespace('neptune', __DIR__ . '/../');
}
?>

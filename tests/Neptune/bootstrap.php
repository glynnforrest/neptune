<?php

namespace Neptune;
use Neptune\Core\Loader;
/**
 * test_bootstrap
 * @author Glynn Forrest <me@glynnforrest.com>
 * This will load just the Loader for test classes to use.
 */
if(!class_exists('\\Neptune\\Core\\Loader')) {
	require(__DIR__ . '/../core/Loader.php');
	spl_autoload_register(__NAMESPACE__ . '\Core\Loader::load');
	Loader::addNamespace('neptune', __DIR__ . '/../');
}
?>

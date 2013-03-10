<?php

namespace Neptune;

use Neptune\Core\Loader;

require(__DIR__ . '/core/Loader.php');
spl_autoload_register(__NAMESPACE__ . '\Core\Loader::load');
Loader::addNamespace('neptune', __DIR__ . '/');
?>

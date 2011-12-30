<?php

namespace neptune;

use neptune\error\ErrorHandler;
use neptune\core\Loader;

require(__DIR__ . '/core/Loader.php');
spl_autoload_register(__NAMESPACE__ . '\core\Loader::load');
Loader::addNamespace('neptune', __DIR__ . '/');
ErrorHandler::init();
?>

<?php
return function(\Neptune\Routing\Router $router, $module, $prefix) {
    $router->globals()->controller("::$module.controller.bar");
    $router->route("$prefix/login")->method("{$module}_module_method");
    $router->name('secret')->route("$prefix/secret")->method('secretArea');
};
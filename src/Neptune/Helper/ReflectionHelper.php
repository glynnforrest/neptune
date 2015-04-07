<?php

namespace Neptune\Helper;

/**
 * ReflectionHelper
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ReflectionHelper
{
    /**
     * Get the arguments of a function as a string, including default
     * arguments and type hints.
     *
     * @return string
     */
    public function formatArguments($function)
    {
        if (is_array($function)) {
            if (!method_exists($function[0], $function[1])) {
                return;
            }
            $reflection = new \ReflectionMethod($function[0], $function[1]);
        } elseif (is_object($function) && is_callable($function)) {
            $reflection = new \ReflectionMethod($function, '__invoke');
        } elseif (function_exists($function)) {
            $reflection = new \ReflectionFunction($function);
        } elseif (is_string($function) && preg_match('{^(.+)::(.+)$}', $function, $m) && method_exists($m[1], $m[2])) {
            $reflection = new \ReflectionMethod($m[1], $m[2]);
        } else {
            throw new \InvalidArgumentException('Invalid callable supplied');
        }

        $args = $reflection->getParameters();

        $args = array_map(function ($param) {
            $class = $param->getClass();
            $name = $class ? $class->getName().' $'.$param->getName() : '$'.$param->getName();

            if ($param->isDefaultValueAvailable()) {
                return $name.' = '.json_encode($param->getDefaultValue());
            }

            return $name;
        }, $args);

        $signature = '(';
        foreach ($args as $arg) {
            $signature .= $arg.', ';
        }

        return trim($signature, ', ').')';
    }
}

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
     * Get the parameters of a function as a string, including default
     * parameters and type hints.
     *
     * @return string
     */
    public function displayFunctionParameters($function)
    {
        return $this->displayParameters($this->getParameters($function));
    }

    /**
     * Get the parameters of a method or function.
     *
     * @return <ReflectionParameter> An array of parameters
     */
    public function getParameters($function)
    {
        if (is_array($function)) {
            if (!method_exists($function[0], $function[1])) {
                throw new \InvalidArgumentException('Invalid callable supplied');
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

        return $reflection->getParameters();
    }

    /**
     * Convert a list of ReflectionParameters to a string representing
     * a function definition, including default parameters and type
     * hints.
     *
     * @param <ReflectionParameter> $parameters
     * @return string
     */
    public function displayParameters(array $parameters)
    {
        $parameters = array_map(function ($param) {
            $class = $param->getClass();
            $name = $class ? $class->getName().' $'.$param->getName() : '$'.$param->getName();

            if ($param->isDefaultValueAvailable()) {
                return $name.' = '.json_encode($param->getDefaultValue());
            }

            return $name;
        }, $parameters);

        $signature = '(';
        foreach ($parameters as $param) {
            $signature .= $param.', ';
        }

        return trim($signature, ', ').')';
    }
}

<?php

namespace Neptune\Validate\Rule;

use Neptune\Validate\Result;

use Stringy\Stringy;

/**
 * AbstractRule
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class AbstractRule
{

    protected $message;

    abstract public function validate(Result $result, $name, $value, array $input = array());

    /**
     * Add $this->message to $result, automatically substituting
     * $name, $value and any additional paramaters supplied in
     * $context.
     */
    protected function fail(Result $result, $name, $value = null, array $context = array())
    {
        $context[':name'] = $this->sensible($name);

        if (is_scalar($value)) {
            $context[':value'] = (string) $value;
        }

        $message = str_replace(array_keys($context), array_values($context), $this->message);
        $result->addError($name, $message);
        return false;
    }

    /**
     * Create a sensible, human readable representation for $name.
     *
     * @param string $name the name to transform
     */
    protected function sensible($name)
    {
        return ucfirst(
            (string) Stringy::create($name)
            ->underscored()
            ->replace('_', ' ')
            ->trim()
        );
    }

}

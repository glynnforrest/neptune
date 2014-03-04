<?php

namespace Neptune\Validate\Rule;

use Neptune\Validate\Rule\AbstractRule;
use Neptune\Validate\Result;

/**
 * Integer
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Integer extends AbstractRule
{

    protected $message = ':name must be an integer.';

    public function __construct($message = null)
    {
        if ($message) {
            $this->message = $message;
        }
    }

    public function validate(Result $result, $name, $value, array $input = array())
    {
        if (filter_var($value, FILTER_VALIDATE_INT) == false) {
            return $this->fail($result, $name, $value);
        }

        return true;
    }

}

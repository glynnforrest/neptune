<?php

namespace Neptune\Validate\Rule;

use Neptune\Validate\Rule\AbstractRule;
use Neptune\Validate\Result;

/**
 * Required
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Required extends AbstractRule
{

    protected $message = ':name is required.';

    public function __construct($message = null)
    {
        if ($message) {
            $this->message = $message;
        }
    }

    public function validate(Result $result, $name, $value, array $input = array())
    {
        if (is_string($value)) {
            $value = trim($value);
        }
        if (empty($value) && !is_numeric($value)) {
            return $this->fail($result, $name, $value);
        }
        return true;
    }

}

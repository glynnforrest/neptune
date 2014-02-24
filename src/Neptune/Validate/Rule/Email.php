<?php

namespace Neptune\Validate\Rule;

use Neptune\Validate\Rule\AbstractRule;
use Neptune\Validate\Result;

/**
 * Email
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Email extends AbstractRule
{

    protected $message = ':name must be a valid email address.';

    public function __construct($message = null)
    {
        if ($message) {
            $this->message = $message;
        }
    }

    public function validate(Result $result, $name, $value, array $input = array())
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return $this->fail($result, $name, $value);
        }

        return true;
    }

}

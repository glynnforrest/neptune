<?php

namespace Neptune\Validate\Rule;

use Neptune\Validate\Rule\Regex;
use Neptune\Validate\Result;

/**
 * AlphaNumeric
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AlphaNumeric extends Regex
{

    protected $message = ':name must be alphanumeric.';
    protected $pattern = '/^[\pL\pN]+$/u';

    public function __construct($message = null)
    {
        if ($message) {
            $this->message = $message;
        }
    }

}

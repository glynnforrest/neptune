<?php

namespace Neptune\Validate\Rule;

use Neptune\Validate\Rule\AbstractRule;
use Neptune\Validate\Result;

/**
 * Matches
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Matches extends AbstractRule
{

    protected $message = ':name does not match :other.';
    protected $other;

    public function __construct($other, $message = null)
    {
        $this->other = $other;
        if ($message) {
            $this->message = $message;
        }
    }

    public function validate(Result $result, $name, $value, array $input = array())
    {
        if (!isset($input[$this->other])) {
            return $this->fail($result, $name, $value, array(':other' => $this->other));
        }

        if ($value !== $input[$this->other]) {
            return $this->fail($result, $name, $value, array(':other' => $this->other));
        }

        return true;
    }

}

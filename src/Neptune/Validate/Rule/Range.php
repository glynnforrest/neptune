<?php

namespace Neptune\Validate\Rule;

use Neptune\Validate\Rule\AbstractRule;
use Neptune\Validate\Result;

/**
 * Range
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Range extends AbstractRule
{

    protected $message = ':name must be between :min and :max.';
    protected $min;
    protected $max;

    public function __construct($min, $max, $message = null)
    {
        $this->min = $min;
        $this->max = $max;
        if ($message) {
            $this->message = $message;
        }
    }

    public function validate(Result $result, $name, $value, array $input = array())
    {
        if (is_numeric($value) && $value >= $this->min && $value <= $this->max) {
            return true;
        }

        return $this->fail($result, $name, $value, array(
            ':min' => $this->min,
            ':max' => $this->max));
    }

}

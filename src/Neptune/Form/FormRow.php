<?php

namespace Neptune\Form;

use Neptune\Helpers\Html;

/**
 * FormRow
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FormRow extends AbstractFormRow
{

    public function __construct($type, $name, $value = null, $options = array())
    {
        //automatically add a value to a submit field
        if ($type === 'submit' && $value === null) {
            $value = $this->sensible($name);
        }
        parent::__construct($type, $name, $value, $options);
    }

    /**
     * Render the input attached to this FormRow as Html.
     */
    public function input()
    {
        if ($this->type === 'select') {
            $selected = $this->value;

            return Html::select($this->name, $this->choices, $selected, $this->options);
        }

        switch ($this->type) {
        //if input is a checkbox and it has a truthy value, add
        //checked to options before render
        case 'checkbox':
            if ($this->value !== null) {
                $this->addOptions(array('checked'));
            }
            //no matter what, the value of the input is 'checked'
            $value = 'checked';
            break;
        case 'password':
            //remove the value from all password fields
            $value = null;
            break;
        case 'date':
            //parse the value as a datetime
            //add day
            //add month
            //add year
        default:
            $value = $this->value;
        }

        return Html::input($this->type, $this->name, $value, $this->options);
    }
    /**
     * Render this FormRow instance as Html, with label, input and
     * error message, if available.
     */
    public function render()
    {
        //a hidden field should be just an input
        if ($this->type == 'hidden') {
            return $this->input();
        }
        //a submit field should be just an input, but with extra html
        //set in $this->row_string
        if ($this->type == 'submit') {
            $str = str_replace(':error', '', $this->row_string);
            $str = str_replace(':label', '', $str);
            $str = str_replace(':input', $this->input(), $str);

            return $str;
        }
        //otherwise, substitute :label, :input and :error into
        //$this->row_string
        $str = str_replace(':label', $this->label(), $this->row_string);
        $str = str_replace(':error', $this->error(), $str);
        $str = str_replace(':input', $this->input(), $str);

        return $str;
    }

    public static function getSupportedTypes()
    {
        return array(
            'checkbox',
            'hidden',
            'password',
            'radio',
            'select',
            'submit',
            'text'
        );
    }

}

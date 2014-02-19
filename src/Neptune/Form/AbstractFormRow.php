<?php

namespace Neptune\Form;

use Stringy\Stringy as S;
use Neptune\Helpers\Html;

/**
 * AbstractFormRow
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class AbstractFormRow
{

    protected $type;
    protected $name;
    protected $value;
    //only applicable for types that support it
    protected $choices = array();
    protected $options;
    protected $label;
    protected $error;
    protected $row_string = ':label:input:error';

    public function __construct($type, $name, $value = null, $options = array())
    {
        if (!in_array($type, static::getSupportedTypes())) {
            throw new \Exception(sprintf(
                '%s does not support type "%s"',
                get_class($this),
                $type));
        }
        $this->type = $type;
        $this->name = $name;
        $this->label = $this->sensible($name);
        $this->setValue($value);
        $this->options = $options;
    }

    /**
     * Create a sensible, human readable default for $string,
     * e.g. creating a label for the name of form inputs.
     *
     * @param string $string the string to transform
     */
    protected function sensible($string)
    {
        return ucfirst(
            (string) S::create($string)
            ->underscored()
            ->replace('_', ' ')
            ->trim()
        );
    }

    /**
     * Set the error message attached to this FormRow.
     *
     * @param string $error The error message.
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get the error message attached to this FormRow.
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set the label text attached to this FormRow.
     *
     * @param string $label The label.
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the label text attached to this FormRow.
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Render the label attached to this FormRow as Html.
     */
    public function label()
    {
        return Html::label($this->name, $this->label);
    }

    /**
     * Set the value of the input attached to this FormRow.
     *
     * @param string $value The value.
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the value of the input attached to this FormRow.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the type of input attached to this FormRow.
     *
     * @param string $type The input type.
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the type of input attached to this FormRow.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the html options of the input attached to this FormRow. All
     * previous options will be reset.
     *
     * @param array $options An array of keys and values
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Add to the html options of the input attached to this FormRow.
     *
     * @param array $options An array of keys and values
     */
    public function addOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * Get the html options of the input attached to this FormRow.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Return true if the type of this FormRow can use choices, and
     * throw an Exception if not.
     */
    protected function checkCanUseChoices()
    {
        if ($this->type === 'select' || $this->type === 'radio') {
            return true;
        }
        throw new \Exception("Form row '$this->name' with type '$this->type' does not support choices");
    }

    /**
     * Set the choices for the input attached to this FormRow. If no
     * keys are given in the choices array or, due to PHP's array
     * implementation, keys are strings containing valid integers,
     * keys will be created automatically by calling
     * FormRow::sensible. An Exception will be thrown if the type of
     * this FormRow does not support choices.
     *
     * @param array $choices An array of keys and values to use in
     * option tags
     */
    public function setChoices(array $choices)
    {
        $this->choices = array();
        $this->addChoices($choices);

        return $this;
    }

    /**
     * Add to the choices for the input attached to this FormRow. If
     * no keys are given in the choices array or, due to PHP's array
     * implementation, keys are strings containing valid integers,
     * keys will be created automatically by calling
     * FormRow::sensible. An Exception will be thrown if the type of
     * this FormRow does not support choices.
     *
     * @param array $choices An array of keys and values to use in
     * option tags
     */
    public function addChoices(array $choices)
    {
        $this->checkCanUseChoices();
        foreach ($choices as $k => $v) {
            if (is_int($k)) {
                $k = $this->sensible($v);
            }
            $this->choices[$k] = $v;
        }

        return $this;
    }

    /**
     * Get the choices for the input attached to this FormRow.
     */
    public function getChoices()
    {
        return $this->choices;
    }

    abstract public function input();

    abstract public function render();

    public static function getSupportedTypes() {
        return array();
    }

}

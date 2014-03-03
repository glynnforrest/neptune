<?php

namespace Neptune\Form;

use Neptune\Helpers\Html;
use Neptune\Validate\Validator;
use Neptune\Database\Thing;
use Neptune\Validate\Rule\AbstractRule;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Form
{
    protected $dispatcher;
    protected $types = array();
    protected $action;
    protected $method;
    protected $options;
    protected $rows = array();
    protected $validator;
    protected $valid = false;
    protected $bound = array();

    public function __construct(EventDispatcherInterface $dispatcher, $action, $method = 'POST', $options = array())
    {
        $this->dispatcher = $dispatcher;
        $this->setHeader($action, $method, $options);
        $this->validator = new Validator();
        $this->init();
        $this->sendEvent(FormEvent::CREATE);
    }

    protected function init()
    {
        $this->addFormRow('Neptune\Form\FormRow');
    }

    public function getId()
    {
        return get_class($this);
    }

    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Set the action attribute of this Form.
     *
     * @param string $action The action.
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get the action attribute of this Form.
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the method attribute of this Form. An exception will be
     * throw if $method is not an allowed http method.
     *
     * @param string $method The method.
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);
        if ($method !== 'POST' && $method !== 'GET') {
            throw new \Exception("Invalid method passed to Form::setMethod: $method");
        }
        $this->method = $method;

        return $this;
    }

    /**
     * Get the method attribute of this Form.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the options of this Form, such as class or id.
     *
     * @param array $options The options.
     */
    public function setOptions(array $options = array())
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get the options attribute of this Form, such as class or id.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the action, method and any additional options of the Form.
     *
     * @param string $action  The action.
     * @param string $method  The method.
     * @param array  $options The options.
     */
    public function setHeader($action, $method = 'POST', array $options = array())
    {
        $this->setAction($action);
        $this->setMethod($method);
        $this->setOptions($options);

        return $this;
    }

    /**
     * Render the header of this Form as Html.
     */
    public function header()
    {
        $options = array('action' => $this->action, 'method' => $this->method);
        $options = array_merge($options, $this->options);

        return Html::openTag('form', $options);
    }

    /**
     * Render the label of FormRow $name as Html.
     *
     * @param string $name The name of the FormRow label to render.
     */
    public function label($name)
    {
        return $this->getRow($name)->label();
    }

    /**
     * Render the input of FormRow $name as Html.
     *
     * @param string $name The name of the FormRow input to render.
     */
    public function input($name)
    {
        return $this->getRow($name)->input();
    }

    /**
     * Render the error of FormRow $name as Html.
     *
     * @param string $name The name of the FormRow error to render.
     */
    public function error($name)
    {
        return $this->getRow($name)->error();
    }

    /**
     * Render the FormRow $name as Html.
     *
     * @param string $name The name of the FormRow render.
     */
    public function row($name)
    {
        return $this->getRow($name)->render();
    }

    /**
     * Render the entire Form as Html.
     */
    public function render()
    {
        $form = $this->header();
        foreach ($this->rows as $row) {
            $form .= $row->render();
        }
        $form .= '</form>';

        return $form;
    }

    public function __toString()
    {
        return $this->render();
    }

    protected function addRow($type, $name, $value = null, $options = array())
    {
        if (!isset($this->types[$type])) {
            throw new \Exception(sprintf('Form type "%s" not registered', $type));
        }
        $class = $this->types[$type];
        $this->rows[$name] = new $class($type, $name, $value, $options);

        return $this;
    }

    /**
     * Get the FormRow instance with name $name.
     *
     * @param string $name The name of the FormRow instance to get.
     */
    public function getRow($name)
    {
        if (!array_key_exists($name, $this->rows)) {
            throw new \Exception(
                "Attempting to access unknown form row '$name'"
            );
        }

        return $this->rows[$name];
    }

    /**
     * Get a list of field names in this form.
     *
     * @return array An array of field names.
     */
    public function getFields()
    {
        return array_keys($this->rows);
    }

    /**
     * Set the value of the input attached to FormRow $name. If
     * the row doesn't exist and $create_row is true, a new FormRow
     * will be created with type 'text'.
     *
     * @param string $name       The name of the FormRow
     * @param string $value      The value
     * @param bool   $create_row Create a new FormRow if it doesn't exist
     */
    public function setValue($name, $value, $create_row = false)
    {
        if (!array_key_exists($name, $this->rows)) {
            if ($create_row) {
                return $this->text($name, $value);
            }

            return $this;
        }
        $this->rows[$name]->setValue($value);

        return $this;
    }

    /**
     * Get the value of the input attached to FormRow $name.
     */
    public function getValue($name)
    {
        return $this->getRow($name)->getValue();
    }

    /**
     * Set the value of the input in multiple FormRows. If any row
     * doesn't exist and $create_rows is true, new FormRows will be
     * created with type 'text'.
     *
     * @param array $values     An array of keys and values to set
     * @param bool  $create_row Create a new FormRow if it doesn't exist
     */
    public function setValues(array $values = array(), $create_rows = false)
    {
        foreach ($values as $name => $value) {
            $this->setValue($name, $value, $create_rows);
        }

        return $this;
    }

    /**
     * Get the values of all inputs attached to this form.
     */
    public function getValues()
    {
        $values = array();
        foreach ($this->rows as $name => $row) {
            //some rows may need to be represented as arrays, so
            //create arrays as needed using parse_str
            parse_str('values[' . preg_replace('`\[`', '][', $name, 1) . ']=' . $row->getValue());
        }

        return $values;
    }

    /**
     * Set the error of FormRow $name.
     *
     * @param string $name  The name of the FormRow
     * @param string $error The error message
     */
    public function setError($name, $error)
    {
        return $this->getRow($name)->setError($error);
    }

    /**
     * Get the error of FormRow $name.
     *
     * @param string $name The name of the FormRow
     */
    public function getError($name)
    {
        return $this->getRow($name)->getError();
    }

    /**
     * Add multiple errors to this Form. $errors should be an array of
     * keys and values, where a key is a name of a FormRow attached to
     * this form, and a value is the error message.
     *
     * @param array $errors An array of names and errors
     */
    public function setErrors(array $errors = array())
    {
        foreach ($errors as $name => $msg) {
            $this->setError($name, $msg);
        }
    }

    /**
     * Get all of the errors attached to this Form.
     *
     * @return array An array of errors
     */
    public function getErrors()
    {
        return array_map(function ($row) {
            return $row->getError();
            },
        $this->rows);
    }

    public function __call($method, array $args)
    {
        array_unshift($args, $method);

        return call_user_func_array(array($this, 'addRow'), $args);
    }

    public function validate(array $values)
    {
        $this->setValues($values);

        foreach ($this->bound as $thing) {
            $thing->setValues($values);
        }

        $this->sendEvent(FormEvent::PRE_VALIDATE);

        $result = $this->validator->validateForm($values);
        if ($result->isValid()) {
            $this->valid = true;
        } else {
            $this->valid = false;
            $this->setErrors($result->getFirstErrors());
        }

        $this->sendEvent(FormEvent::POST_VALIDATE);

        return $result;
    }

    protected function matchesRows(array $values)
    {
        foreach (array_keys($this->rows) as $name) {
            if (!isset($values[$name])) {
                return false;
            }
        }

        return true;
    }

    public function handle(Request $request)
    {
        //get the correct method
        if ($this->method === 'GET') {
            $values = $request->query->all();
        } else {
            $values = $request->request->all();
        }

        if (!$this->matchesRows($values)) {
            return true;
        }
        $this->validate($values);
    }

    protected function sendEvent($event_name)
    {
        if ($this->dispatcher->hasListeners($event_name)) {
            $event = new FormEvent($this);
            $this->dispatcher->dispatch($event_name, $event);
        }
    }

    public function check($name, AbstractRule $rule)
    {
        $this->validator->check($name, $rule);

        return $this;
    }

    public function bind(Thing $thing)
    {
        //set the values of the form to the values of thing
        $this->bound[] = $thing;
        $this->setValues($thing->getValues());
    }

    public function isValid()
    {
        return $this->valid;
    }

    public function addFormRow($class)
    {
        foreach ($class::getSupportedTypes() as $type) {
            $this->types[$type] = $class;
        }
    }

}

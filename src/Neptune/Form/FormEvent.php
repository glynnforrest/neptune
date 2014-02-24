<?php

namespace Neptune\Form;

use Symfony\Component\EventDispatcher\Event;
use Neptune\Form\Form;

/**
 * FormEvent
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FormEvent extends Event
{

    const CREATE = 'form.create';
    const PRE_VALIDATE = 'form.pre-validate';
    const POST_VALIDATE = 'form.post-validate';

    protected $form;

    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    public function getForm()
    {
        return $this->form;
    }

}

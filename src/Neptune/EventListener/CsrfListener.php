<?php

namespace Neptune\EventListener;

use Neptune\Core\Neptune;
use Neptune\Security\Csrf\CsrfManager;
use Reform\Form\FormEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * CsrfListener
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CsrfListener implements EventSubscriberInterface
{

    protected $manager;
    protected $form_field;

    public function __construct(CsrfManager $manager, $form_field = '_token')
    {
        $this->manager = $manager;
        $this->form_field = $form_field;
    }

    public function onFormCreate(FormEvent $event)
    {
        $form = $event->getForm();
        $id = $form->getId();
        $this->manager->maybeInit($id);
        $form->hidden($this->form_field, $this->manager->get($id));
    }

    public function afterFormValidate(FormEvent $event)
    {
        $form = $event->getForm();
        $token = $form->getValue($this->form_field);
        if (!$form->isValid()) {
            return;
        }
        $id = $form->getId();
        $this->manager->check($id, $token);
        //the token is valid and is now removed. Generate a new token
        //in case the form needs to be submitted again.
        $form->setValue($this->form_field, $this->manager->init($id));
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvent::CREATE => array('onFormCreate'),
            FormEvent::POST_VALIDATE => array('afterFormValidate')
        );
    }

}

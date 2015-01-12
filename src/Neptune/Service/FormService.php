<?php

namespace Neptune\Service;

use Neptune\Core\Neptune;
use Neptune\Form\FormCreator;
use Reform\Csrf\CsrfChecker;
use Reform\EventListener\CsrfListener;

/**
 * FormService
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FormService implements ServiceInterface
{
    public function register(Neptune $neptune)
    {
        $neptune['form'] = function ($neptune) {
            //register csrf by default
            if ($neptune['config']->get('security.csrf', true)) {
                $neptune['dispatcher']->addSubscriber($neptune['security.csrf']);
            }

            return new FormCreator($neptune, $neptune['dispatcher']);
        };

        $neptune['security.csrf'] = function ($neptune) {
            $config = $neptune['config'];

            //get the session and form tokens
            $session_token = $config->get('security.csrf.session_token', 'security.csrf.token');
            $form_token = $config->get('security.csrf.form_token', '_token');

            $manager = new CsrfChecker($neptune['session'], $session_token);

            return new CsrfListener($manager, $form_token, true);
        };
    }

    public function boot(Neptune $neptune)
    {
    }
}

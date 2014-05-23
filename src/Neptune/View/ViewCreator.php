<?php

namespace Neptune\View;

use Neptune\Core\Neptune;

/**
 * ViewCreator
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ViewCreator
{

    /**
     * @var Neptune
     */
    protected $neptune;

    public function __construct(Neptune $neptune)
    {
        $this->neptune = $neptune;
    }

    public function load($view, array $values = array())
    {
        $pos = strpos($view, ':');
        if ($pos) {
            $module = substr($view, 0, $pos);
            $view = substr($view, $pos + 1);
        } else {
            $module = $this->neptune->getDefaultModule();
        }
        $template = sprintf('%sviews/%s.php', $this->neptune->getModuleDirectory($module), $view);
        $view = new View($template);
        $view->setValues($values);
        $view->setCreator($this);

        return $view;
    }

}

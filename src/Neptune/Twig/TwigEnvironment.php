<?php

namespace Neptune\Twig;

/**
 * TwigEnvironment wraps the parent with some additional helpers.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TwigEnvironment extends \Twig_Environment
{
    /**
     * Check if the given template exists.
     *
     * @param string $template
     * @return bool
     */
    public function exists($template)
    {
        if ($this->loader instanceof \Twig_ExistsLoaderInterface) {
            return $this->loader->exists($template);
        }

        try {
            $this->loader->getSource($template);
        } catch (\Twig_Error_Loader $e) {
            return false;
        }

        return true;
    }
}

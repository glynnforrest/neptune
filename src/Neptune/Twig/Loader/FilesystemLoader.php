<?php

namespace Neptune\Twig\Loader;

use Neptune\Core\Neptune;

/**
 * FilesystemLoader
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FilesystemLoader extends \Twig_Loader_Filesystem
{
    protected $neptune;

    public function __construct(Neptune $neptune)
    {
        $this->neptune = $neptune;
    }

    /**
     * Get the path to a template file.
     *
     * @param string $template The template
     *
     * @return string The path to the template file
     *
     * @throws \Twig_Error_Loader if the template could not be found
     */
    protected function findTemplate($template)
    {
        $pos = strpos($template, ':');
        if (!$pos) {
            //no module has been supplied, so use the app directory
            $file = sprintf('%sapp/views/%s', $this->neptune->getRootDirectory(), $template);
            $this->assertFile($file);

            return $file;
        }

        //the template is in a module
        $module = substr($template, 0, $pos);
        $template = substr($template, $pos + 1);

        //check for an overriding template in the app/ folder
        $override = sprintf('%sapp/views/%s/%s', $this->neptune->getRootDirectory(), $module, $template);
        if (file_exists($override)) {
            return $override;
        }

        $file = sprintf('%sviews/%s', $this->neptune->getModuleDirectory($module), $template);
        $this->assertFile($file);

        return $file;
    }

    private function assertFile($file)
    {
        if (!file_exists($file)) {
            throw new \Twig_Error_Loader(sprintf('Unable to find template "%s".', $file));
        }
    }
}

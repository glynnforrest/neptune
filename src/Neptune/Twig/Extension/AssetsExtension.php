<?php

namespace Neptune\Twig\Extension;

use Neptune\Assets\AssetManager;

/**
 * AssetsExtension
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AssetsExtension extends \Twig_Extension
{
    protected $manager;

    public function __construct(AssetManager $manager)
    {
        $this->manager = $manager;
    }

    public function getName()
    {
        return 'assets';
    }

    public function getFunctions()
    {
        $options = ['is_safe' => ['html']];

        return [
            new \Twig_SimpleFunction('js', [$this, 'js'], $options),
            new \Twig_SimpleFunction('css', [$this, 'css'], $options),
        ];
    }

    public function js()
    {
        return $this->manager->js();
    }

    public function css()
    {
        return $this->manager->css();
    }
}

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
            new \Twig_SimpleFunction('js', [$this->manager, 'js'], $options),
            new \Twig_SimpleFunction('inlineJs', [$this->manager, 'inlineJs'], $options),
            new \Twig_SimpleFunction('jsGroup', [$this->manager, 'jsGroup'], $options),
            new \Twig_SimpleFunction('css', [$this->manager, 'css'], $options),
            new \Twig_SimpleFunction('inlineCss', [$this->manager, 'inlineCss'], $options),
            new \Twig_SimpleFunction('cssGroup', [$this->manager, 'cssGroup'], $options),
        ];
    }
}

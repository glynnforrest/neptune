<?php

namespace Neptune\View\Extension;

use Neptune\Assets\Assets;
use Neptune\View\Extension\ExtensionInterface;

/**
 * AssetsExtension
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AssetsExtension implements ExtensionInterface
{

    protected $assets;

    public function __construct(Assets $assets)
    {
        $this->assets = $assets;
    }

    public function getHelpers()
    {
        return [
            'js' => 'js',
            'css' => 'css',
            'assets' => 'assets'
        ];
    }

    public function assets()
    {
        return $this->assets;
    }

    public function js()
    {
        return $this->assets->js();
    }

    public function css()
    {
        return $this->assets->css();
    }

}

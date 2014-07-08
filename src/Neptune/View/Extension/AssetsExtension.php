<?php

namespace Neptune\View\Extension;

use Neptune\Assets\AssetManager;
use Neptune\View\Extension\ExtensionInterface;

/**
 * AssetsExtension
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AssetsExtension implements ExtensionInterface
{

    protected $manager;

    public function __construct(AssetManager $manager)
    {
        $this->manager = $manager;
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
        return $this->manager;
    }

    public function js($group = AssetManager::DEFAULT_GROUP)
    {
        return $this->manager->js($group);
    }

    public function css($group = AssetManager::DEFAULT_GROUP)
    {
        return $this->manager->css($group);
    }

}

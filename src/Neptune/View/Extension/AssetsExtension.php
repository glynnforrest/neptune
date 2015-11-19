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
            'css' => [$this->manager, 'css'],
            'inlineCss' => [$this->manager, 'inlineCss'],
            'cssGroup' => [$this->manager, 'cssGroup'],
            'js' => [$this->manager, 'js'],
            'inlineJs' => [$this->manager, 'inlineJs'],
            'jsGroup' => [$this->manager, 'jsGroup'],
        ];
    }
}

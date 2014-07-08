<?php

namespace Neptune\Assets;

use Neptune\Assets\TagGenerator;

/**
 * AssetManager
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetManager
{
    const DEFAULT_GROUP = '__default';

    protected $group = self::DEFAULT_GROUP;
    protected $generator;
    protected $js = [];
    protected $css = [];
    protected $concat;

    public function __construct(TagGenerator $generator, $concatenate = false)
    {
        $this->generator = $generator;
        $this->concat = $concatenate;
    }

    /**
     * Create a unique identifier of a group name for an asset
     * file. Override this method to define a different group asset
     * schema.
     */
    protected function hashGroup($group, $type)
    {
        return md5($group) . '.' . $type;
    }

    public function addCss($src, array $options = array())
    {
        $this->css[$this->group][] = [$src, $options];
    }

    public function addCssGroup($group, array $assets)
    {
        $this->css[$group] = [];
        $this->group = $group;
        foreach ($assets as $asset) {
            $this->addCss($asset);
        }
    }

    public function css($group = self::DEFAULT_GROUP)
    {
        if ($this->concat && $group !== self::DEFAULT_GROUP) {
            return $this->generator->css($this->hashGroup($group, 'css'));
        }
        $content ='';
        foreach ($this->css[$group] as $css) {
            $content .= $this->generator->css($css[0], $css[1]);
        }

        return $content;
    }

    public function addJs($src, array $options = array())
    {
        $this->js[$this->group][] = [$src, $options];
    }

    public function addJsGroup($group, array $assets)
    {
        $this->js[$group] = [];
        $this->group = $group;
        foreach ($assets as $asset) {
            $this->addJs($asset);
        }
    }

    public function js($group = self::DEFAULT_GROUP)
    {
        if ($this->concat && $group !== self::DEFAULT_GROUP) {
            return $this->generator->js($this->hashGroup($group, 'js'));
        }
        $content ='';
        foreach ($this->js[$group] as $js) {
            $content .= $this->generator->js($js[0], $js[1]);
        }

        return $content;
    }

    public function clear()
    {
        $this->js = array();
        $this->css = array();
    }

}

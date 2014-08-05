<?php

namespace Neptune\Assets;

use Neptune\Assets\TagGenerator;
use Neptune\Config\ConfigManager;

/**
 * AssetManager
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetManager
{

    protected $config;
    protected $generator;

    protected $concat;
    protected $css = [];
    protected $js = [];

    public function __construct(ConfigManager $config, TagGenerator $generator, $concatenate = false)
    {
        $this->config = $config;
        $this->generator = $generator;
        $this->concat = (bool) $concatenate;
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

    /**
     * Locate an asset group, either from a registered group or a
     * module config file.
     */
    protected function locateGroup($group, $type)
    {
        $pos = strpos($group, ':');
        if (!$pos) {
            throw new \Exception("Asset group not found: $group");
        }
        $module = substr($group, 0, $pos);
        $name = substr($group, $pos + 1);

        $assets = $this->config->load($module)->getRequired("assets.$type.$name");
        if (!is_array($assets)) {
            throw new \Exception("Asset group $group is not an array");
        }

        return $assets;
    }

    public function addCss($src)
    {
        $this->css[] = $src;
    }

    public function addCssGroup($name)
    {
        if ($this->concat) {
            $this->css[] = $this->hashGroup($name, 'css');

            return;
        }
        $this->css = array_merge($this->css, $this->locateGroup($name, 'css'));
    }

    public function css()
    {
        $content ='';
        foreach ($this->css as $css) {
            $content .= $this->generator->css($css);
        }

        return $content;
    }

    public function addJs($src)
    {
        $this->js[] = $src;
    }

    public function addJsGroup($name)
    {
        if ($this->concat) {
            $this->js[] = $this->hashGroup($name, 'js');

            return;
        }
        $this->js = array_merge($this->js, $this->locateGroup($name, 'js'));
    }

    public function js()
    {
        $content ='';
        foreach ($this->js as $js) {
            $content .= $this->generator->js($js);
        }

        return $content;
    }

    public function clear()
    {
        $this->css = [];
        $this->js = [];
    }

}

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
    const DEFAULT_GROUP = '__default';

    protected $config;
    protected $generator;

    protected $assets = [];
    protected $concat;

    public function __construct(ConfigManager $config, TagGenerator $generator, $concatenate = false)
    {
        $this->config = $config;
        $this->generator = $generator;
        $this->concat = (bool) $concatenate;
        $this->clear();
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
        if (isset($this->assets[$type][$group])) {
            return $this->assets[$type][$group];
        }

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
        $this->assets[$type][$group] = $assets;

        return $assets;
    }

    public function addCss($src, $group = self::DEFAULT_GROUP)
    {
        $this->assets['css'][$group][] = $src;
    }

    public function addCssGroup($group, array $assets)
    {
        $this->assets['css'][$group] = $assets;
    }

    public function css($group = self::DEFAULT_GROUP)
    {
        if ($this->concat && $group !== self::DEFAULT_GROUP) {
            return $this->generator->css($this->hashGroup($group, 'css'));
        }
        $content ='';
        foreach ($this->locateGroup($group, 'css') as $css) {
            $content .= $this->generator->css($css);
        }

        return $content;
    }

    public function addJs($src, $group = self::DEFAULT_GROUP)
    {
        $this->assets['js'][$group][] = $src;
    }

    public function addJsGroup($group, array $assets)
    {
        $this->assets['js'][$group] = $assets;
    }

    public function js($group = self::DEFAULT_GROUP)
    {
        if ($this->concat && $group !== self::DEFAULT_GROUP) {
            return $this->generator->js($this->hashGroup($group, 'js'));
        }
        $content ='';
        foreach ($this->locateGroup($group, 'js') as $js) {
            $content .= $this->generator->js($js);
        }

        return $content;
    }

    public function clear()
    {
        $this->assets = ['css' => [], 'js' => []];
        $this->assets['css'][self::DEFAULT_GROUP] = [];
        $this->assets['js'][self::DEFAULT_GROUP] = [];
    }

}

<?php

namespace Neptune\Assets;

use Neptune\Config\ConfigManager;

/**
 * AssetManager
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetManager
{

    const LINK = 1;
    const INLINE = 2;

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

    public function concatenate($concatenate = true)
    {
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
     * Locate an asset group from a module config file.
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

        return array_map(function ($asset) {
            return [self::LINK, $asset];
        }, $assets);
    }

    public function addCss($src)
    {
        $this->css[] = [self::LINK, $src];
    }

    public function addCssGroup($name)
    {
        if ($this->concat) {
            $this->css[] = [self::LINK, $this->hashGroup($name, 'css')];

            return;
        }
        $this->css = array_merge($this->css, $this->locateGroup($name, 'css'));
    }

    /**
     * Add an inline css tag.
     *
     * @param string $css The css code
     */
    public function addInlineCss($css)
    {
        $this->css[] = [self::INLINE, $css];
    }

    public function css()
    {
        $content ='';
        foreach ($this->css as $css) {
            $content .= $css[0] === self::INLINE ? $this->generator->inlineCss($css[1]) : $this->generator->css($css[1]);
        }

        return $content;
    }

    public function addJs($src)
    {
        $this->js[] = [self::LINK, $src];
    }

    public function addJsGroup($name)
    {
        if ($this->concat) {
            $this->js[] = [self::LINK, $this->hashGroup($name, 'js')];

            return;
        }
        $this->js = array_merge($this->js, $this->locateGroup($name, 'js'));
    }

    /**
     * Add an inline javascript tag.
     *
     * @param string $js The javascript code
     */
    public function addInlineJs($js)
    {
        $this->js[] = [self::INLINE, $js];
    }

    public function js()
    {
        $content ='';
        foreach ($this->js as $js) {
            $content .= $js[0] === self::INLINE ? $this->generator->inlineJs($js[1]) : $this->generator->js($js[1]);
        }

        return $content;
    }

    public function clear()
    {
        $this->css = [];
        $this->js = [];
    }

}

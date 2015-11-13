<?php

namespace Neptune\Assets;

use Neptune\Config\Config;
use Neptune\Service\AbstractModule;
use Symfony\Component\Filesystem\Filesystem;

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

    public function __construct(Config $config, TagGenerator $generator, $concatenate = false)
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
    public function hashGroup($group, $type)
    {
        return md5($group . $type) . '.' . $type;
    }

    /**
     * Get a list of all assets in a given group.
     *
     * @param string $group The name of the group - <module>:<name>
     * @param string $type  The type of assets - css or js
     */
    protected function getGroupAssets($group, $type)
    {
        $pos = strpos($group, ':');
        if (!$pos) {
            throw new \Exception("Asset group not found: $group");
        }
        $module = substr($group, 0, $pos);
        $name = substr($group, $pos + 1);

        $assets = $this->config->getRequired("$module.assets.$type.$name");
        if (!is_array($assets)) {
            throw new \Exception("Asset group $group is not an array");
        }

        //loop through the given assets, treating any that begin with
        //'@' as a group.
        $results = [];
        foreach ($assets as $asset) {
            if (substr($asset, 0, 1) === '@') {
                $results = array_merge($results, $this->getGroupAssets(substr($asset, 1), $type));
            } else {
                $results[] = $asset;
            }
        }

        return $results;
    }

    /**
     * Get a list of all assets in a given group, formatted for
     * internal AssetManager usage.
     *
     * @param string $group The name of the group - <module>:<name>
     * @param string $type  The type of assets - css or js
     */
    protected function getGroupAssetsFormatted($group, $type)
    {
        return array_map(function ($asset) {
            return [self::LINK, $asset];
        }, $this->getGroupAssets($group, $type));
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
        $this->css = array_merge($this->css, $this->getGroupAssetsFormatted($name, 'css'));
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
        $this->js = array_merge($this->js, $this->getGroupAssetsFormatted($name, 'js'));
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

    /**
     * Create concatenated asset files for a module. A file will be
     * created for every asset group in the module configuration.
     *
     * Assets must already exist in $build_directory
     * (usually the public folder).
     *
     * @param string $module_name
     * @param string $build_directory The directory to place the concatenated assets
     */
    public function concatenateAssets($module_name, $build_directory)
    {
        $filesystem = new Filesystem();
        $filesystem->mkdir($build_directory, 0755);

        foreach (['css', 'js'] as $type) {
            $groups = array_keys($this->config->get("$module_name.assets.$type", []));

            foreach ($groups as $group) {
                $group_name = $module_name . ':' . $group;
                $files = $this->getGroupAssets($group_name, $type);

                foreach ($files as $file) {
                    if (!file_exists($build_directory . $file)) {
                        throw new \Exception(sprintf('%s not found. Unable to concatenate asset group %s.', $build_directory. $file, $group_name));
                    }
                }

                $group_file = new \SplFileObject($build_directory . $this->hashGroup($group_name, $type), 'w');
                foreach ($files as $file) {
                    $group_file->fwrite(file_get_contents($build_directory . $file));
                    $group_file->fwrite(PHP_EOL . PHP_EOL);
                }

                //Free the handle on the file (no close method
                //exists). Since this method may be called in a long
                //running process (the cli), this prevents
                //accidentally locking the file and preventing
                //deletion. Better to be safe than smelly.
                $group_file = null;
            }
        }
    }

    /**
     * Run the command to install assets for a module.
     *
     * @param AbstractModule $module
     */
    public function installAssets(AbstractModule $module)
    {
        if (!$command = $this->config->get("{$module->getName()}.assets.install_cmd", false)) {
            return false;
        }

        $dir = $module->getDirectory();

        passthru("cd $dir && $command");

        return true;
    }
}

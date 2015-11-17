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
    protected $config;
    protected $generator;

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

    public function css($src)
    {
        return $this->generator->css($src);
    }

    public function cssGroup($name)
    {
        if ($this->concat) {
            return $this->generator->css($this->hashGroup($name, 'css'));
        }
        $html = '';
        foreach ($this->getGroupAssets($name, 'css') as $css) {
            $html .= $this->generator->css($css);
        }

        return $html;
    }

    /**
     * Add an inline css tag.
     *
     * @param string $css The css code
     */
    public function inlineCss($css)
    {
        return $this->generator->inlineCss($css);
    }

    /**
     * Render a javascript tag.
     *
     * @param string $src The source of the javascript file
     */
    public function js($src)
    {
        return $this->generator->js($src);
    }

    /**
     * Render an inline javascript tag.
     *
     * @param string $js The javascript code
     */
    public function inlineJs($js)
    {
        return $this->generator->inlineJs($js);
    }

    /**
     * Render javascript tags for all files in an asset group.
     */
    public function jsGroup($name)
    {
        if ($this->concat) {
            return $this->generator->js($this->hashGroup($name, 'js'));
        }
        $html = '';
        foreach ($this->getGroupAssets($name, 'js') as $js) {
            $html .= $this->generator->js($js);
        }

        return $html;
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

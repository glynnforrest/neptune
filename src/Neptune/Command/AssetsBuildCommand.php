<?php

namespace Neptune\Command;

use Neptune\Output\Output;
use Neptune\Assets\Asset;

use Neptune\Config\Exception\ConfigFileException;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AssetsBuildCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AssetsBuildCommand extends AssetsInstallCommand
{
    protected $name = 'assets:build';
    protected $description = 'Build module assets and link to the public folder';
    protected $build_dir;
    protected $progress;
    protected $assets_count;

    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description)
             ->addArgument(
                 'modules',
                 InputArgument::IS_ARRAY,
                 'A list of modules to build instead of all.'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupBuildDir($output);
        $modules = $this->getModulesToProcess($input);
        $manager = $this->neptune['assets'];

        foreach ($modules as $name => $module) {
            $output->writeln('');
            $config = $this->neptune['config'];

            if (!$command = $config->get("$name.assets.build_cmd", false)) {
                $output->writeln("Skipping <info>$name</info>");
                continue;
            }

            $output->writeln("Building <info>$name</info>");
            $dir = $module->getDirectory();

            passthru("cd $dir && $command");

            //move built assets into public dir
            $build_dir = $dir . 'assets_built/';
            if (!file_exists($build_dir)) {
                throw new \Exception(sprintf('Assets build command for "%s" must create directory "%s"', $name, $build_dir));
            }

            $public_dir = $this->neptune->getRootDirectory() . 'public/assets/';
            $group_dir = $public_dir . $name;
            if (file_exists($group_dir)) {
                unlink($group_dir);
            }

            symlink($build_dir, $group_dir);
            $output->writeln(sprintf('Linking %s to %s', $build_dir, $group_dir));

            //generate concatenated asset files
            $manager->concatenateAssets($name, $public_dir);
            $output->writeln('Creating concatenated group files');
        }
        $output->writeln('');
        $output->writeln(sprintf('Built assets to <info>%s</info>', $this->build_dir));
    }

    protected function setupBuildDir(OutputInterface $output)
    {
        $build_dir = $this->getRootDirectory() . 'public/' . $this->config->get('assets.url', 'assets/');
        //make sure build_dir has a trailing slash
        if (substr($build_dir, -1) !== '/') {
            $build_dir .= '/';
        }
        //create build_dir if it doesn't exist
        if (!file_exists($build_dir)) {
            mkdir($build_dir, 0755, true);
            $output->writeln("Creating $build_dir");
        }
        if (!is_dir($build_dir) | !is_writeable($build_dir)) {
            throw new \Exception(
                "Unable to write to $build_dir. Check file paths and permissions are correct.");
        }
        $this->build_dir = $build_dir;
    }

}

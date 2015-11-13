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
    protected $description = 'Concatenate group assets in a module and place in the public folder';
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
            $manager->concatenateAssets($name, $this->build_dir);
        }

        $output->writeln(sprintf('Built concatenated assets to <info>%s</info>', $this->build_dir));

        //check if assets.concat is true, otherwise write a helper msg
        if (true !== $concat = $this->config->get('neptune.assets.concat_groups')) {
            $output->writeln('');
            $output->writeln(sprintf('Config setting <info>neptune.assets.concat_groups</info> needs to be set to <info>true</info> (currently <info>%s</info>)', var_export($concat, true)));
        }
    }

    protected function setupBuildDir(OutputInterface $output)
    {
        $build_dir = $this->getRootDirectory() . 'public/' . $this->config->get('assets.url', 'assets/');
        //make sure build_dir has a trailing slash
        $build_dir = rtrim($build_dir, '/').'/';

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

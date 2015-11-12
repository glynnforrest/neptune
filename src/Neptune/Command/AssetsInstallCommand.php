<?php

namespace Neptune\Command;

use Neptune\Config\Exception\ConfigFileException;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * AssetsInstallCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AssetsInstallCommand extends Command
{
    protected $name = 'assets:install';
    protected $description = 'Install module assets';

    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description)
             ->addArgument(
                 'modules',
                 InputArgument::IS_ARRAY,
                 'A list of modules to install instead of all.'
             );
    }

    protected function getModulesToProcess(InputInterface $input)
    {
        $args = $input->getArgument('modules');
        if (!$args) {
            return $this->neptune->getModules();
        }
        $modules = [];
        foreach ($args as $name) {
            $modules[$name] = $this->neptune->getModule($name);
        }

        return $modules;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $asset_manager = $this->neptune['assets'];
        $modules = $this->getModulesToProcess($input);

        foreach ($modules as $name => $module) {
            if (!$asset_manager->installAssets($module)) {
                $output->writeln("Skipping <info>$name</info>");
                continue;
            }

            $output->writeln("Installed <info>$name</info>");
        }

        //link each group to the public directory
        $build_dir = $this->setupBuildDir($output);

        foreach ($modules as $name => $module) {
            $src = $module->getDirectory() . 'assets';
            if (!is_dir($src)) {
                continue;
            }

            $target = $build_dir . $name;
            if (file_exists($target)) {
                if (!is_link($target)) {
                    throw new \Exception(sprintf('Unable to link to %s - %s already exists.', $src, $target));
                }
                unlink($target);
            }

            symlink($src, $target);
            $output->writeln(sprintf('Linked <info>%s</info> to <info>%s</info>', $src, $target));
        }

        $output->writeln('');
        $output->writeln('Installed assets');
    }

    protected function setupBuildDir(OutputInterface $output)
    {
        $build_dir = $this->getRootDirectory() . 'public/' . $this->config->get('assets.url', 'assets/');
        //make sure build_dir has a trailing slash
        if (substr($build_dir, -1) !== '/') {
            $build_dir .= '/';
        }
        if (!file_exists($build_dir)) {
            mkdir($build_dir, 0755, true);
            $output->writeln("Creating $build_dir");
        }
        if (!is_dir($build_dir) | !is_writeable($build_dir)) {
            throw new \Exception(
                "Unable to write to $build_dir. Check file paths and permissions are correct.");
        }

        return $build_dir;
    }
}

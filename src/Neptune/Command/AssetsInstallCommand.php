<?php

namespace Neptune\Command;

use Neptune\Config\Exception\ConfigFileException;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

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
             )
             ->addOption(
                 'no-link',
                 '',
                 InputOption::VALUE_NONE,
                 'Don\'t link the assets into the public folder'
             )
             ->addOption(
                 'no-install',
                 '',
                 InputOption::VALUE_NONE,
                 'Don\'t run the install commands in each module'
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
        if (!$input->getOption('no-install')) {
            $this->install($input, $output);
        }

        if (!$input->getOption('no-link')) {
            $this->link($input, $output);
        }
    }

    protected function install(InputInterface $input, OutputInterface $output)
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
    }

    /**
     * Link each group to the public directory
     */
    protected function link(InputInterface $input, OutputInterface $output)
    {
        $modules = $this->getModulesToProcess($input);
        $build_dir = $this->getRootDirectory() . 'public/' . $this->config->get('neptune.assets.url', 'assets/');
        $filesystem = new Filesystem();

        foreach ($modules as $name => $module) {
            $src = $module->getDirectory() . 'assets';
            if (!is_dir($src)) {
                continue;
            }

            $target = $build_dir . $name;
            if (file_exists($target) && !is_link($target)) {
                throw new \Exception(sprintf('Unable to link to %s - %s already exists.', $src, $target));
            }

            $filesystem->symlink($src, $target);
            $output->writeln(sprintf('Linked <info>%s</info> to <info>%s</info>', $src, $target));
        }
    }
}

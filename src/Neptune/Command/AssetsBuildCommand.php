<?php

namespace Neptune\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AssetsBuildCommand.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AssetsBuildCommand extends AssetsInstallCommand
{
    protected $name = 'assets:build';
    protected $description = 'Concatenate group assets in a module and place in the public folder';

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
        $build_dir = $this->neptune['assets.build_dir'];

        $modules = $this->getModulesToProcess($input);
        $manager = $this->neptune['assets'];

        foreach ($modules as $name => $module) {
            $manager->concatenateAssets($name, $build_dir);
        }

        $output->writeln(sprintf('Built concatenated assets to <info>%s</info>', $build_dir));

        //check if assets.concat is true, otherwise write a helper msg
        if (true !== $concat = $this->config->get('neptune.assets.concat_groups')) {
            $output->writeln('');
            $output->writeln(sprintf('Config setting <info>neptune.assets.concat_groups</info> needs to be set to <info>true</info> (currently <info>%s</info>)', var_export($concat, true)));
        }
    }
}

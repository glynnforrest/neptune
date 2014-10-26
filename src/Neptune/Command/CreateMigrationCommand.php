<?php

namespace Neptune\Command;

use Neptune\View\Skeleton;

use Stringy\Stringy;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CreateMigrationCommand
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CreateMigrationCommand extends CreateCommand
{

    protected $name = 'create:migration';
    protected $description = 'Create a new migration';
    protected $migration_description;

    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description)
             ->addArgument(
                 'description',
                 InputArgument::OPTIONAL,
                 'The description of the migration.'
             )
             ->addOption(
                 'module',
                 'm',
                 InputOption::VALUE_REQUIRED,
                 'The module to place the file.',
                 $this->getDefaultModule()
             );
    }

    protected function getTargetPath($name)
    {
        return 'Migrations/' . $name . '.php';
    }

    protected function getResourceName(InputInterface $input, OutputInterface $output)
    {
        $description = $input->getArgument('description');
        if (!$description) {
            $dialog = $this->getHelper('dialog');
            $description = $dialog->ask($output, 'Description for this migration: ');
        }

        $this->migration_description = str_replace("'", "\'", $description);

        return 'Migration' . date('YmdHis') . Stringy::create($description)->slugify()->upperCamelize();
    }

    protected function getSkeleton($name)
    {
        $skeleton = new Skeleton($this->getSkeletonPath('migration'));
        $skeleton->class_name = $name;
        $skeleton->description = $this->migration_description;

        return $skeleton;
    }

}

<?php

namespace Neptune\Command;

use Neptune\Config\Config;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Stringy\StaticStringy as S;

/**
 * CreateModuleCommand
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CreateModuleCommand extends Command
{
    protected $name = 'create:module';
    protected $description = 'Create a new module';
    protected $module_directory;

    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description)
             ->addArgument(
                 'name',
                 InputArgument::OPTIONAL,
                 'The name of the new module.'
             )
             ->addArgument(
                 'namespace',
                 InputArgument::OPTIONAL,
                 'The class namespace of the new module.'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelper('dialog');
        $name = $input->getArgument('name');
        if (!$name) {
            $name = $dialog->ask($output, 'Name of module: ');
        }
        $name = strtolower($name);

        $namespace = $input->getArgument('namespace');
        if (!$namespace) {
            $namespace = $dialog->ask($output, 'Namespace for this module: ', S::upperCamelize($name));
        }

        $this->createDirectories($output, $this->createModuleDirectory($namespace));

        $file = $this->createModuleDirectory($namespace) . 'config.php';
        $config = new Config($name);
        $config->save($file);
        $output->writeln("Created <info>$file</info>");

        //create module class
    }

    protected function createModuleDirectory($namespace, $absolute = true)
    {
        if (substr($namespace, -1) !== '/') {
            $namespace .= '/';
        }
        if ($absolute) {
            return $this->getRootDirectory() . 'src/' . $namespace;
        }

        return 'src/' . $namespace;
    }

    protected function createDirectories(OutputInterface $output, $root)
    {
        $dirs = array(
            'views',
            'assets'
        );
        foreach ($dirs as $dir) {
            $dir = $root . $dir;
            if (file_exists($dir)) {
                continue;
            }
            mkdir($dir, 0755, true);
            if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(sprintf("Creating directory <info>%s</info>", $dir));
            }
        }
    }

}

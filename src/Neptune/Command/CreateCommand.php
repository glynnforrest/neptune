<?php

namespace Neptune\Command;

use Neptune\View\Skeleton;
use Neptune\Exceptions\FileException;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Stringy\StaticStringy as S;

/**
 * CreateCommand
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class CreateCommand extends Command
{
    protected $prompt = 'Resource name: ';
    protected $default = 'Home';

    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description)
             ->addArgument(
                 'module',
                 InputArgument::OPTIONAL,
                 'The module of the new resource.'
             )
             ->addArgument(
                 'name',
                 InputArgument::OPTIONAL,
                 'The name of the new resource.'
             )
             ->addOption(
                 'with-test',
                 't',
                 InputOption::VALUE_NONE,
                 'Also create a test file for the new resource.'
             )
             ->addOption(
                 'test-only',
                 'T',
                 InputOption::VALUE_NONE,
                 'Create a test file instead of the new resource.'
             );
    }

    /**
     * Get the path of the resource to create, relative to the module
     * directory.
     */
    abstract protected function getTargetPath($name);

    /**
     * Get a skeleton instance with all required variables set.
     */
    abstract protected function getSkeleton($name);

    protected function getSkeletonPath($skeleton)
    {
        return $this->neptune->getRootDirectory() . 'vendor/glynnforrest/neptune/' . 'skeletons/' . $skeleton . '.php';
    }

    protected function getResourceName(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        if (!$name) {
            $dialog = $this->getHelper('dialog');
            $name = $dialog->ask($output, $this->prompt, $this->default);
        }

        return $name;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $module = $this->getModuleArgument($input, $output);

        $name = $this->getResourceName($input, $output);
        $skeleton = $this->getSkeleton($name);
        $skeleton->setNamespace($module->getNamespace());

        $target_file = $module->getDirectory() . $this->getTargetPath($name);
        $directory = dirname($target_file);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
            if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(sprintf('Created directory <info>%s</info>', $directory));
            }

        }
        $this->saveSkeletonToFile($output, $skeleton, $target_file);
    }

    protected function saveSkeletonToFile(OutputInterface $output, Skeleton $skeleton, $file)
    {
        $create_msg = "Created <info>$file</info>";
        try {
            $skeleton->save($file);
            $output->writeln($create_msg);
        } catch (FileException $e) {
            //ask to overwrite the file
            $overwrite = $this->getHelper('dialog')->askConfirmation($output, "<info>$file</info> exists. Overwrite? ", false);
            if ($overwrite) {
                $skeleton->save($file, true);
                $output->writeln($create_msg);
            }
        }
    }

}

<?php

namespace Neptune\Command;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Neptune\Config\Config;

/**
 * SetupCommand
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SetupCommand extends SymfonyCommand
{
    protected $name = 'setup';
    protected $description = 'Setup a new neptune application';

    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description)
             ->addArgument(
                 'directory',
                 InputArgument::REQUIRED,
                 'The path to the application.'
             )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = rtrim($input->getArgument('directory'), '/').'/';
        $output->writeln(sprintf('Creating neptune install at <info>%s</info>', $dir));
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('%s is not a directory.', substr($dir, 0, -1)));
        }

        $this->createDirectories($output, $dir);
        $this->populateNeptuneConfig($output, $dir.'config/neptune.php');
        $files_to_copy = [
            'neptune' => 'neptune',
        ];
        foreach ($files_to_copy as $source => $target) {
            $this->copyFile($input, $output, $dir.'vendor/glynnforrest/neptune/'.$source, $dir.$target);
        }
    }

    protected function createDirectories(OutputInterface $output, $root)
    {
        $dirs = [
            'app',
            'config/modules',
            'config/env',
            'src',
            'public',
            'storage/logs',
        ];
        foreach ($dirs as $dir) {
            $dir = $root.$dir;
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
                $output->writeln(sprintf('Creating directory <info>%s</info>', $dir));
            }
        }
    }

    protected function populateNeptuneConfig(OutputInterface $output, $path)
    {
        //a list of neptune config values to set as a starter. Flatten
        //the config so the output messages are more meaningful.
        $values = [
            'env' => 'development',
            'routing.root_url' => 'myapp.dev/',
        ];

        //load config if it already exists
        $config = new Config('neptune');

        foreach ($values as $key => $value) {
            $config->set($key, $value);
            if (is_string($value) && !empty($value)) {
                $msg = sprintf("Neptune config: Setting <info>%s</info> to <info>%s</info>", $key, $value);
            } else {
                $msg = sprintf("Neptune config: Setting <info>%s</info>", $key);
            }
            if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($msg);
            }
        }

        $config->save($path);
    }

    protected function copyFile(InputInterface $input, OutputInterface $output, $source, $target)
    {
        if (file_exists($target)) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(sprintf('<info>%s</info> exists. Overwrite? ', $target), false);

            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }

        copy($source, $target);
        chmod($target, 0755);
        $output->writeln(sprintf('Copied <info>%s</info> to <info>%s</info>', $source, $target));
    }
}

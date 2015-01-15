<?php

namespace Neptune\Command;

use Neptune\Exceptions\FileException;
use Neptune\Config\Config;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * EnvCreateCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EnvCreateCommand extends Command
{

    protected $name = 'env:create';
    protected $description = 'Create a yaml configuration for a new environment';

    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description)
             ->addArgument(
                 'name',
                 InputArgument::OPTIONAL,
                 'The name of the new environment.'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $dialog = $this->getHelper('dialog');
        if (!$name) {
            $name = $dialog->ask($output, 'Name of environment: ');
        }
        try {
            $this->newEnv($output, $name);
        } catch (FileException $e) {
            if (!$input->isInteractive()) {
                throw $e;
            }

            $overwrite = $dialog->askConfirmation($output, "<info>$name</info> exists. Overwrite? ", false);
            if ($overwrite) {
                $this->newEnv($output, $name, true);
            }
        }
    }

    protected function newEnv(OutputInterface $output, $name, $overwrite = false)
    {
        $name = strtolower($name);
        $file = $this->getRootDirectory() . 'config/env/' . $name . '.yml';
        if (file_exists($file) && !$overwrite) {
            throw new FileException("$file already exists");
        }

        $values = [
            'routing.root_url' => 'myapp.dev/',
            'database.main.driver' => 'pdo_mysql',
            'database.main.dbname' => 'sandbox',
            'database.main.user' => 'root',
            'database.main.pass' => '',
            'database.main.logger' => 'logger',
        ];

        $config = new Config();

        foreach ($values as $key => $value) {
            $config->set($key, $value);
            if (!$output->isVerbose()) {
                continue;
            }
            if (is_string($value) && !empty($value)) {
                $msg = sprintf("Config: Setting <info>%s</info> to <info>%s</info>", $key, $value);
            } else {
                $msg = sprintf("Config: Setting <info>%s</info>", $key);
            }
            $output->writeln($msg);
        }

        $yaml = Yaml::dump($config->get(), 100, 2);
        file_put_contents($file, $yaml);

        $output->writeln("Created <info>$file</info>");
    }
}

<?php

namespace Neptune\Command;

use Neptune\Exceptions\FileException;
use Neptune\Config\Config;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * EnvCreateCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EnvCreateCommand extends Command
{

    protected $name = 'env:create';
    protected $description = 'Create a new application configuration';

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
            $overwrite = $dialog->askConfirmation($output, "<info>$name</info> exists. Overwrite? ", false);
            if ($overwrite) {
                $this->newEnv($output, $name, true);
            }
        }
    }

    protected function newEnv(OutputInterface $output, $name, $overwrite = false)
    {
        $name = strtolower($name);
        $config = $this->getRootDirectory() . 'config/env/' . $name . '.php';
        if (file_exists($config)) {
            if (!$overwrite) {
                throw new FileException("Environment $name already exists");
            }
        }
        //hacky method for now until Config can create a new file.
        //set('rooting.root_url', '');
        file_put_contents($config, '<?php return array ();');
        $output->writeln("Created <info>$config</info>");
    }

}

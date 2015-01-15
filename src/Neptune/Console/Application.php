<?php

namespace Neptune\Console;

use Neptune\Core\Neptune;

use \DirectoryIterator;
use \CallbackFilterIterator;
use \ReflectionClass;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Helper\ProgressHelper;

use SensioLabs\Security\SecurityChecker;
use SensioLabs\Security\Command\SecurityCheckerCommand;

use Stringy\StaticStringy as S;
use Neptune\Config\Exception\ConfigFileException;

/**
 * Application
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Application extends SymfonyApplication
{
    protected $neptune;
    protected $commands_registered;

    public function __construct(Neptune $neptune)
    {
        $this->neptune = $neptune;
        parent::__construct('Neptune', Neptune::NEPTUNE_VERSION);
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        //potentially override the environment
        if ($input->hasParameterOption(array('--env', '-e'))) {
            $this->neptune->setEnv($input->getParameterOption(array('--env', '-e')));
        }
        if ($output->isVeryVerbose() && $this->neptune->getEnv()) {
            $output->writeln(sprintf('Using environment <info>%s</info>', $this->neptune->getEnv()));
        }

        //load the app configuration now to give a useful message if
        //it fails
        try {
            $this->neptune['config'];
        } catch (ConfigFileException $e) {
            $this->renderException($e, $output);
            $output->writeln('Run `<info>./vendor/bin/neptune-install .</info>` to set up a default configuration.');

            return;
        }

        if (!$this->commands_registered) {
            $this->registerCommands($output);
        }

        return parent::doRun($input, $output);
    }

    /**
     * Register Commands in the neptune 'Command' directory and from
     * any loaded modules
     */
    protected function registerCommands(OutputInterface $output)
    {
        $this->registerNamespace('Neptune', __DIR__ . '/../Command/');

        if (class_exists('\SensioLabs\Security\SecurityChecker')) {
            $this->add(new SecurityCheckerCommand(new SecurityChecker()));
        }

        foreach ($this->neptune->getModules() as $module) {
            $namespace = $module->getNamespace();
            $path = $module->getDirectory().'Command/';
            if (!file_exists($path)) {
                continue;
            }

            try {
                $this->registerNamespace($namespace, $path);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>%s: %s</error>', get_class($e), $e->getMessage()));
            }

        }
        $this->commands_registered = true;
    }

    /**
     * Register all Command classes in $command_dir with
     * $namespace. It is assumed that commands have the class name
     * $namespace\Command\<Foo>Command and extend
     * Neptune\Command\Command.
     *
     * @param string $namespace   The namespace of commands to register.
     * @param string $command_dir The directory containing the command classes.
     */
    public function registerNamespace($namespace, $command_dir)
    {
        if (!is_dir($command_dir)) {
            throw new \InvalidArgumentException(sprintf('%s does not exist', $command_dir));
        }
        $i = new DirectoryIterator($command_dir);
        //Possible commands must be files that end in Command.php
        $candidates = new CallbackFilterIterator($i, function ($current, $key, $iterator) {
            return $current->isFile() && substr($current->getFilename(), -11) === 'Command.php';
        });
        foreach ($candidates as $file) {
            $class = $namespace . '\\Command\\' . $file->getBasename('.php');
            $r = new ReflectionClass($class);
            if ($r->isSubclassOf('\Symfony\Component\Console\Command\Command') && !$r->isAbstract()) {
                if ($r->isSubclassOf('\Neptune\Command\Command')) {
                    $this->add($r->newInstance($this->neptune));
                } else {
                    $this->add($r->newInstance());
                }
            }
        }
    }

    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $option = new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The name of the environment.');
        $definition->addOption($option);

        return $definition;
    }

}

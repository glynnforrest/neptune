<?php

namespace Neptune\Console;

use Neptune\Config\Config;
use Neptune\Console\DialogHelper as NeptuneDialogHelper;
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

/**
 * Application
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Application extends SymfonyApplication
{
    protected $neptune;
    protected $config;
    protected $commands_registered;

    public function __construct(Neptune $neptune)
    {
        $this->neptune = $neptune;
        $this->config = $neptune['config'];
        parent::__construct('Neptune', '0.2.5');
        $this->useNeptuneHelperSet();
    }

    public function useNeptuneHelperSet()
    {
        $this->setHelperSet(new HelperSet(array(
            new FormatterHelper(),
            new NeptuneDialogHelper(),
            new ProgressHelper(),
            new TableHelper(),
        )));
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
        if ($input->hasParameterOption(array('--env', '-e'))) {
            $env = $input->getParameterOption(array('--env', '-e'));
        } else {
            $env = $this->config->get('env');
        }
        if ($env) {
            $this->neptune->loadEnv($env);
            if ($output->isVeryVerbose()) {
                $output->writeln("Using environment <info>$env</info>");
            }
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
        $this->registerNamespace('Neptune', $this->neptune->getRootDirectory() . 'vendor/glynnforrest/neptune/src/Neptune/Command/');

        if (class_exists('\SensioLabs\Security\SecurityChecker')) {
            $this->add(new SecurityCheckerCommand(new SecurityChecker()));
        }

        foreach ($this->neptune->getModules() as $module) {
            $namespace = $module->getNamespace();
            $path = $module->getDirectory();
            try {
                $this->registerNamespace($namespace, $path . 'Command/');
            } catch (\Exception $e) {
                $output->writeln(sprintf('Warning: %s', $e->getMessage()));
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
            return false;
        }
        $i = new DirectoryIterator($command_dir);
        //Possible commands must be files that end in Command.php
        $candidates = new CallbackFilterIterator($i, function ($current, $key, $iterator) {
            return $current->isFile() && substr($current->getFilename(), -11) === 'Command.php';
        });
        foreach ($candidates as $file) {
            $class = $namespace . '\\Command\\' . $file->getBasename('.php');
            try {
                $r = new ReflectionClass($class);
                if ($r->isSubclassOf('\Symfony\Component\Console\Command\Command') && !$r->isAbstract()) {
                    if ($r->isSubclassOf('\Neptune\Command\Command')) {
                        $this->add($r->newInstance($this->neptune));
                    } else {
                        $this->add($r->newInstance());
                    }
                }
            } catch (\ReflectionException $e) {
                continue;
            }
        }
    }

    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $option = new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The name of the environment, instead of the default in config/neptune.php');
        $definition->addOption($option);

        return $definition;
    }

}

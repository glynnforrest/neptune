<?php

namespace Neptune\Console;

use Neptune\Core\Config;
use Neptune\Exceptions\ClassNotFoundException;
use Neptune\Console\Shell;
use Neptune\Console\DialogHelper as NeptuneDialogHelper;

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

use Stringy\StaticStringy as S;

/**
 * Application
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Application extends SymfonyApplication {

	protected $config;
	protected $commandsRegistered;

	public function __construct(Config $config) {
		$this->config = $config;
		parent::__construct('Neptune', '0.2.4');
		$this->useNeptuneHelperSet();
	}

	public function useNeptuneHelperSet() {
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
	public function doRun(InputInterface $input, OutputInterface $output) {
		if (!$this->commandsRegistered) {
			$this->registerCommands();
		}
		return parent::doRun($input, $output);
	}

	/**
	 * Register Commands in the neptune 'Command' directory and from
	 * the modules set in neptune.php
	 */
	public function registerCommands() {
		$this->registerNamespace('Neptune', $this->config->get('dir.neptune') . 'src/Neptune/Command/');
		$root = $this->config->getRequired('dir.root');
		foreach ($this->config->get('modules') as $module => $path) {
			$namespace = $this->getModuleNamespace($module);
			$this->registerNamespace($namespace, $root . $path . 'Command/');
		}
	}

	/**
	 * Register all Command classes in $command_dir with
	 * $namespace. It is assumed that commands have the class name
	 * $namespace\Command\<Foo>Command and extend
	 * Neptune\Command\Command.
	 *
	 * @param string $namespace The namespace of commands to register.
	 * @param string $command_dir The directory containing the command classes.
	 */
	public function registerNamespace($namespace, $command_dir) {
		$i = new DirectoryIterator($command_dir);
		//Possible commands must be files that end in Command.php
		$candidates = new CallbackFilterIterator($i, function ($current, $key, $iterator) {
			return $current->isFile() && substr($current->getFilename(), -11) === 'Command.php';
		});
		foreach ($candidates as $file) {
			$class = $namespace . '\\Command\\' . $file->getBasename('.php');
			try {
				$r = new ReflectionClass($class);
				if ($r->isSubclassOf('Neptune\\Command\\Command') && !$r->isAbstract()) {
					$this->add($r->newInstance($this->config));
				}
			} catch (\ReflectionException $e) {
				continue;
			}
		}
	}

	/**
	 * Get the namespace of a module with no beginning slash.
	 *
	 * @param string $module the name of the module
	 */
	public function getModuleNamespace($module) {
		$namespace = Config::load($module)->getRequired('namespace');
		if(substr($namespace, 0, 1) === '\\') {
			$namespace = substr($namespace, 0, 1);
		}
		return $namespace;
	}

}

<?php

namespace Neptune\Command;

use Neptune\Command\Command;
use Neptune\Console\Console;
use Neptune\View\Skeleton;
use Neptune\Exceptions\FileException;
use Neptune\Exceptions\ConfigKeyException;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Stringy\StaticStringy as S;

/**
 * CreateCommand
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class CreateCommand extends Command {

	protected $prompt = 'Resource name: ';
	protected $default = 'Home';

	protected function configure() {
		$this->setName($this->name)
			 ->setDescription($this->description)
			 ->addArgument(
				 'name',
				 InputArgument::OPTIONAL,
				 'The name of the new resource.'
				 //make this an array to create loads
			 )
			 ->addOption(
				 'module',
				 'm',
				 InputOption::VALUE_REQUIRED,
				 'The module of the new resource.',
				 $this->getDefaultModule()
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

	protected function getSkeletonPath($skeleton) {
		return $this->config->getRequired('dir.neptune') . 'skeletons/' . $skeleton;
	}

	protected function checkModule() {
		$module = $this->input->getOption('module');
		$this->config->getRequired('modules.' . $module);
		$this->console->verbose(sprintf('Target module: <info>%s</info>', $module));
	}

	public function go(Console $console) {
		try {
			$this->checkModule();
		} catch (ConfigKeyException $e) {
			$console->writeln(sprintf("<error>%s</error>", $e->getMessage()));
			return false;
		}

		$name = $this->input->getArgument('name');
		if(!$name) {
			$dialog = $this->getHelper('dialog');
			$name = $dialog->ask($this->output, $this->prompt, $this->default);
		}
		$skeleton = $this->getSkeleton($name);

		$module = $this->input->getOption('module');
		$skeleton->setNamespace($this->getModuleNamespace($module));
		$target_file = $this->getModuleDirectory($module) .
			$this->getTargetPath($name);
		$this->saveSkeletonToFile($skeleton, $target_file);
	}

	public function isEnabled() {
		return $this->neptuneConfigSetup();
	}

	protected function saveSkeletonToFile(Skeleton $skeleton, $file) {
		$create_msg = "Created <info>$file</info>";
		try {
			$skeleton->saveSkeleton($file);
			$this->output->writeln($create_msg);
		} catch (FileException $e){
			//ask to overwrite the file
			$overwrite = $this->getHelper('dialog')->askConfirmation($this->output, "<info>$file</info> exists. Overwrite? ", false);
			if($overwrite) {
				$skeleton->saveSkeleton($file, true);
				$this->output->writeln($create_msg);
			}
		}
	}

}

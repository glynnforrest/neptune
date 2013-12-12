<?php

namespace Neptune\Command;

use Neptune\Command\Command;
use Neptune\Console\Console;
use Neptune\View\Skeleton;
use Neptune\Exceptions\FileException;

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
	 * Get the file name of the resource to create.
	 */
	abstract protected function getResourceFilename($name);

	abstract protected function getSkeleton($name);

	protected function getSkeletonPath($skeleton) {
		return $this->config->getRequired('dir.neptune') . 'skeletons/' . $skeleton;
	}

	public function go(Console $console) {
		$name = $this->input->getArgument('name');
		if(!$name) {
			$dialog = $this->getHelper('dialog');
			$name = $dialog->ask($this->output, $this->prompt, $this->default);
		}
		$skeleton = $this->getSkeleton($name);
		$new_file = $this->getResourceFilename($name);
		$this->saveSkeletonToFile($skeleton, $new_file);
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

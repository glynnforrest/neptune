<?php

namespace Neptune\Console;

use Neptune\Console\DialogHelper;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * Console is a wrapper around a Symfony console InputInterface,
 * OutputInterface, and HelperSet.
 *
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Console {

	protected $input;
	protected $output;
	protected $helper_set;

	public function __construct(InputInterface $input, OutputInterface $output) {
		$this->input = $input;
		$this->output = $output;
	}

	/**
	 * Set the HelperSet instance used by this Console.
	 *
	 * @param HelperSet $helper_set an instance of a Symfony HelperSet
	 */
	public function setHelperSet(HelperSet $helper_set) {
		$this->helper_set = $helper_set;
	}

	/**
	 * Get the HelperSet instance used by this Console.
	 */
	public function getHelperSet() {
		if(!$this->helper_set) {
			throw new \Exception('HelperSet instance not defined for this Console instance');
		}
		return $this->helper_set;
	}

	/**
	 * Write a message to output.
	 *
     * @param string|array $messages The message as an array of lines
     * or a single string
     * @param Boolean $newline Whether to add a newline
     * @param integer $type The type of output (one of the OUTPUT constants)
	 */
	public function write($messages, $newline = false, $type = OutputInterface::OUTPUT_NORMAL) {
		$this->output->write($messages, $newline, $type);
	}

	/**
	 * Write a message to output.
	 *
     * @param string|array $messages The message as an array of lines
     * or a single string
     * @param integer $type The type of output (one of the OUTPUT constants)
	 */
	public function writeln($messages, $type = OutputInterface::OUTPUT_NORMAL) {
		$this->write($messages, true, $type);
	}

	/**
	 * Write a message to output, but only if the current verbosity
	 * level is verbose or higher.
	 *
     * @param string|array $messages The message as an array of lines
     * or a single string
     * @param Boolean $newline Whether to add a newline
     * @param integer $type The type of output (one of the OUTPUT constants)
	 */
	public function verbose($messages, $newline = true, $type = OutputInterface::OUTPUT_NORMAL) {
		if($this->output->isVerbose()) {
			$this->output->write($messages, $newline, $type);
		}
	}

	/**
	 * Write a message to output, but only if the current verbosity
	 * level is very verbose or higher.
	 *
     * @param string|array $messages The message as an array of lines
     * or a single string
     * @param Boolean $newline Whether to add a newline
     * @param integer $type The type of output (one of the OUTPUT constants)
	 */
	public function veryVerbose($messages, $newline = true, $type = OutputInterface::OUTPUT_NORMAL) {
		if($this->output->isVeryVerbose()) {
			$this->output->write($messages, $newline, $type);
		}
	}

	/**
	 * Write a message to output, but only if the current verbosity
	 * level is debug or higher.
	 *
     * @param string|array $messages The message as an array of lines
     * or a single string
     * @param Boolean $newline Whether to add a newline
     * @param integer $type The type of output (one of the OUTPUT constants)
	 */
	public function debug($messages, $newline = true, $type = OutputInterface::OUTPUT_NORMAL) {
		if($this->output->isDebug()) {
			$this->output->write($messages, $newline, $type);
		}
	}

}

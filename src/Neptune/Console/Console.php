<?php

namespace Neptune\Console;

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
	protected static $output_type = OutputInterface::OUTPUT_NORMAL;

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
	 * Set the default output type of all output to OUTPUT_NORMAL. If
	 * no output type is given to a method that writes to the output,
	 * OUTPUT_NORMAL will be used.
	 */
	public static function outputNormal() {
		self::$output_type = OutputInterface::OUTPUT_NORMAL;
	}

	/**
	 * Set the default output type of all output to OUTPUT_RAW. If
	 * no output type is given to a method that writes to the output,
	 * OUTPUT_RAW will be used. This method can be used for testing
	 * output of a command that contains tags, without modifying the
	 * command itself.
	 */
	public static function outputRaw() {
		self::$output_type = OutputInterface::OUTPUT_RAW;
	}

	/**
	 * Set the default output type of all output to OUTPUT_PLAIN. If
	 * no output type is given to a method that writes to the output,
	 * OUTPUT_PLAIN will be used.
	 */
	public static function outputPlain() {
		self::$output_type = OutputInterface::OUTPUT_PLAIN;
	}

	/**
	 * Write a message to output.
	 *
	 * @param string|array $messages The message as an array of lines
	 * or a single string
	 * @param Boolean $newline Whether to add a newline
	 * @param integer $type The type of output (one of the OUTPUT constants)
	 */
	public function write($messages, $newline = false, $type = null) {
		$type = is_int($type) ? $type : self::$output_type;
		$this->output->write($messages, $newline, $type);
	}

	/**
	 * Write a message to output.
	 *
	 * @param string|array $messages The message as an array of lines
	 * or a single string
	 * @param integer $type The type of output (one of the OUTPUT constants)
	 */
	public function writeln($messages, $type = null) {
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
	public function verbose($messages, $newline = true, $type = null) {
		if($this->output->isVerbose()) {
			$this->write($messages, $newline, $type);
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
	public function veryVerbose($messages, $newline = true, $type = null) {
		if($this->output->isVeryVerbose()) {
			$this->write($messages, $newline, $type);
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
	public function debug($messages, $newline = true, $type = null) {
		if($this->output->isDebug()) {
			$this->write($messages, $newline, $type);
		}
	}

	public function ask($question, $default = null, array $autocomplete = null) {
		return $this->getHelperSet()->get('dialog')->ask($this->output, $question, $default, $autocomplete);
	}

	public function askAndValidate($question, $validator, $attempts = false, $default = null, array $autocomplete = null) {
		return $this->getHelperSet()->get('dialog')->askAndValidate($this->output, $question, $validator, $attempts, $default, $autocomplete);
	}

}

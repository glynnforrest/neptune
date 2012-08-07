<?php

namespace neptune\console;

/**
 * Console
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Console {

	protected static $instance;
	protected $readline;
	protected $prompt_suffix;
	protected $fg_colour;
	protected $bg_colour;
	protected $error_fg_colour;
	protected $error_bg_colour;
	protected $default_options = array();

	protected function __construct() {
		$this->readline = extension_loaded('readline');
		$this->prompt_suffix = PHP_EOL . '=> ';
	}

	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function write($string, $new_line = true) {
		if ($new_line) {
			echo $string . PHP_EOL;
		} else {
			echo $string;
		}
	}

	public function error($string, $new_line = true) {
		if ($new_line) {
			echo 'Error: ' . $string . PHP_EOL;
		} else {
			echo 'Error: ' . $string;
		}
	}

	public function read($prompt = null, $default = null) {
		$text = $this->addDefaultToPrompt($prompt, $default) .
			$this->prompt_suffix;
		if ($this->readline) {
			$input = readline($text);
			$this->setDefaultOption($prompt, $input);
			return $input;
		}
		$this->write($text, false);
		$input = fgets(STDIN);
		$this->setDefaultOption($prompt, $input);
		return $input;
	}

	public function readOptions(array $options, $prompt = null, $default = null) {
		return $this->read($this->options($options, $prompt, $default));
		//todo:check if supplied input matches options.
	}

	public function setPromptSuffix($string) {
		$this->prompt_suffix = $string;
	}


	/** Create a string combining a prompt and an array of options.
	 */
	public function options(array $options, $prompt = null) {
		$prompt = $prompt . ' [' . implode($options, ', ') . ']';
	}

	public function setDefaultOption($prompt, $option) {
		$this->default_options[md5($prompt)] = $option;
	}


	/** Append a default to the end of a prompt.
	 * If default is a string, use that string as a default.
	 * If default is true, the last used answer for this prompt
	 * will be used as a default.
	 * If default is null, don't print a default.
	 *
	 */
	protected function getDefaultOption($prompt, $default = null) {
		if($default === true) {
			if(isset($this->default_options[md5($prompt)])) {
				return $this->default_options[md5($prompt)];
			} else {
				return null;
			}
		} elseif($default) {
			return $default;
		} else {
			return null;
		}
	}

	public function addDefaultToPrompt($prompt, $default = null) {
		$default = $this->getDefaultOption($prompt, $default);
		if($default) {
			$prompt .= ' (Default: '. $default . ')';
		}
		return $prompt;
	}



}
?>

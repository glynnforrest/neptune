<?php

namespace Neptune\Console;

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
		} else {
			$this->write($text, false);
			$input = fgets(STDIN);
		}
		//if input is blank use the default
		if($input == '') {
			$input = $this->getDefaultOption($prompt, $default);
		}
		$this->setDefaultOption($prompt, $input);
		return $input;
	}

	/**
	 * Read input, choosing from an array of options.
	 * The function will only return if the given input is in the options array,
	 * either a number index or the option itself.
	 *
	 * @return string The selected option.
	 */
	public function readOptions(array $options, $prompt = null, $default = null) {
		$prompt = $this->options($options, $prompt);
		while (true) {
			$value = $this->read($prompt, $default);
			if(in_array($value, $options)) {
				return $value;
			} else {
				if (is_numeric($value) && $value < count($options)) {
					$this->setDefaultOption($prompt, $options[$value]);
					return $options[$value];
				}
			}
		}
	}

	public function setPromptSuffix($string) {
		$this->prompt_suffix = $string;
	}


	/**
	 * Create a string combining a prompt and an array of
	 * options, indexed with numbers for easy selection.
	 */
	public function options(array $options, $prompt = null) {
		$prompt .= ' [';
		$count = count($options) - 1;
		for ($i = 0; $i < $count; $i++) {
			$prompt .= $i . ':' . $options[$i] . ', ';
		};
		$prompt .= $count . ':' . $options[$count] . '] ';
		return $prompt;
	}

	public function setDefaultOption($prompt, $option) {
		$this->default_options[md5($prompt)] = $option;
	}

	protected function getDefaultOption($prompt, $default = null) {
		if($default === true) {
			$key = md5($prompt);
			if(isset($this->default_options[$key])) {
				return $this->default_options[$key];
			} else {
				return null;
			}
		}
		if($default !== null) {
			return $default;
		}
		return null;
	}

	/**
	 * Append a default to the end of a prompt.
	 * If default is a string, use that string as a default.
	 * If default is true, the last used answer for this prompt
	 * will be used as a default.
	 * If default is null, don't print a default.
	 *
	 */
	public function addDefaultToPrompt($prompt, $default = null) {
		$default = $this->getDefaultOption($prompt, $default);
		if(!is_null($default) && $default !== '') {
			$prompt .= ' (Default: '. $default . ')';
		}
		return $prompt;
	}



}

<?php

namespace Neptune\Console;

use Symfony\Component\Console\Helper\DialogHelper as SymfonyDialogHelper;

/**
 * DialogHelper wraps the Symfony DialogHelper with some additional
 * features, such as displaying the default choice and remembering the
 * input the last time a question was asked.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DialogHelper extends SymfonyDialogHelper {

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
		while(true) {
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

	/**
	 * Read the answer to a yes/no question.
	 * The function will only return if yes or no is given, although
	 * it ignores case and will match on any word that begins with
	 * either 'y' or 'n'.
	 *
	 * @return bool True on 'yes', False on 'no'.
	 */
	public function readYesNo($prompt = null) {
		$prompt .= ' [Y]es, [N]o :';
		while(true) {
			$value = $this->read($prompt);
			if(strtolower(substr($value, 0, 1)) === 'y') {
				return true;
			}
			if(strtolower(substr($value, 0, 1)) === 'n') {
				return false;
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
		$count = count($options) - 1;
		if($count < 0) {
			throw new ConsoleException(
				'Empty $options array given to Console::options().');
		}
		$prompt .= ' [';
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

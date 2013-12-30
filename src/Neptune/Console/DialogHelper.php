<?php

namespace Neptune\Console;

use Symfony\Component\Console\Helper\DialogHelper as SymfonyDialogHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * DialogHelper wraps the Symfony DialogHelper with some additional
 * features, such as displaying the default choice and remembering the
 * response from the last time a question was asked.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DialogHelper extends SymfonyDialogHelper {

	protected $defaults = array();

	protected function setDefault($question, $option) {
		$this->defaults[md5($question)] = $option;
		return true;
	}

	protected function getDefault($question, $default = null) {
		if($default === true) {
			$key = md5($question);
			if(isset($this->defaults[$key])) {
				return $this->defaults[$key];
			} else {
				return null;
			}
		}
		return $default;
	}

	/**
	 * Append a default to the end of a question.
	 * If default is a string, use that string as a default.
	 * If default is true, the last used answer for this question
	 * will be used as a default.
	 * If default is null, don't print a default.
	 */
	protected function addDefaultToQuestion($question, $default = null) {
		$default = $this->getDefault($question, $default);
		if(!is_null($default) && $default !== '') {
			$question .= '[Default: <info>'. $default . '</info>] ';
		}
		return $question;
	}

	public function ask(OutputInterface $output, $question, $default = null, array $autocomplete = null) {
		$question_with_default = $this->addDefaultToQuestion($question, $default);
		$default = $this->getDefault($question, $default);
		$return = parent::ask($output, $question_with_default, $default, $autocomplete);
		//now we have an answer, set it as the default for next time
		if($return !== null) {
			$this->setDefault($question, $return);
		}
		return $return;
	}

}

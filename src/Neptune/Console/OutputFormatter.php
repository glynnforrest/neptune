<?php

namespace Neptune\Console;

use Symfony\Component\Console\Formatter\OutputFormatter as SymfonyFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * OutputFormatter
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class OutputFormatter extends SymfonyFormatter {

	public function __construct($decorated = false, array $styles = array()) {
		parent::__construct(true, $styles);
		$this->setStyle('logo', new OutputFormatterStyle('magenta'));
		$this->setStyle('error', new OutputFormatterStyle('white', 'red'));
		$this->setStyle('comment', new OutputFormatterStyle('magenta', null, array('underscore')));
		$this->setStyle('info', new OutputFormatterStyle('cyan'));
		$this->setStyle('question', new OutputFormatterStyle('black', 'cyan'));
	}

}

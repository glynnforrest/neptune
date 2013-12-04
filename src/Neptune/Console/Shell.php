<?php

namespace Neptune\Console;

use Neptune\Console\OutputFormatter;

use Symfony\Component\Console\Shell as BaseShell;
use Symfony\Component\Console\Application as SymfonyApplication;

/**
 * Shell
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Shell extends BaseShell {

	public function __construct(SymfonyApplication $application) {
		parent::__construct($application);
		$output = $this->getOutput()->setFormatter(new OutputFormatter());
	}

	/**
	 * Returns the shell header.
	 *
	 * @return string The header string
	 */
	protected function getHeader() {
		return <<<EOF
<logo>
             _   _            _
            | \ | | ___ _ __ | |_ _   _ _ __   ___
 _____ _____|  \| |/ _ \ '_ \| __| | | | '_ \ / _ \_____ _____
|_____|_____| |\  |  __/ |_) | |_| |_| | | | |  __/_____|_____|
            |_| \_|\___| .__/ \__|\__,_|_| |_|\___|
                       |_|
</logo>
EOF
			. parent::getHeader();
	}

	/**
	 * Renders a prompt.
	 *
	 * @return string The prompt
	 */
	protected function getPrompt() {
		return $this->getOutput()->getFormatter()->format('<info>---€</info> ');
	}

}

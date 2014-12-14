<?php

namespace Neptune\Tests\Console;

use Neptune\Console\DialogHelper;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * DialogHelperTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DialogHelperTest extends \PHPUnit_Framework_TestCase {

	protected function getInputStream($input) {
		$stream = fopen('php://memory', 'r+', false);
		fputs($stream, $input);
		rewind($stream);
		return $stream;
	}

	protected function getOutputStream() {
		return new StreamOutput(fopen('php://memory', 'r+', false));
	}

	public function testAsk() {
		$dialog = new DialogHelper();
		$dialog->setInputStream($this->getInputStream("black\n"));
		$this->assertEquals('black', $dialog->ask($output = $this->getOutputStream(), 'Favourite colour? '));
		rewind($output->getStream());
		$this->assertEquals('Favourite colour? ', stream_get_contents($output->getStream()));
	}

	public function testAskEmptyAnswer() {
		$dialog = new DialogHelper();
		$dialog->setInputStream($this->getInputStream("\n"));
		$this->assertEquals(null, $dialog->ask($this->getOutputStream(), 'Favourite colour? '));
	}

	public function testAskWithDefault() {
		$dialog = new DialogHelper();
		$dialog->setInputStream($this->getInputStream("\nblack\n"));
		$this->assertEquals('white', $dialog->ask($this->getOutputStream(), 'Favourite colour? ', 'white'));
		$this->assertEquals('black', $dialog->ask($output = $this->getOutputStream(), 'Favourite colour? ', 'white'));
		rewind($output->getStream());
		$this->assertEquals('Favourite colour? [Default: white] ', stream_get_contents($output->getStream()));
	}

	public function testAskWithPreviousDefault() {
		$dialog = new DialogHelper();
		$dialog->setInputStream($this->getInputStream("\ngreen\n\nred\nred\n"));

		//first no answer is given, so expect null
		$this->assertEquals(null, $dialog->ask($this->getOutputStream(), 'Favourite colour? ', true));
		//green is given, so expect green
		$this->assertEquals('green', $dialog->ask($this->getOutputStream(), 'Favourite colour? ', true));
		//no answer is given, but green is returned as it is the new default
		$this->assertEquals('green', $dialog->ask($green_output = $this->getOutputStream(), 'Favourite colour? ', true));
		rewind($green_output->getStream());
		$this->assertEquals('Favourite colour? [Default: green] ', stream_get_contents($green_output->getStream()));

		//now again with another colour
		//even if the third parameter is missing a default should still be set
		$this->assertEquals('red', $dialog->ask($this->getOutputStream(), 'Favourite colour? '));
		//no answer is given, but we have the new default
		$this->assertEquals('red', $dialog->ask($red_output = $this->getOutputStream(), 'Favourite colour? ', true));
		rewind($red_output->getStream());
		$this->assertEquals('Favourite colour? [Default: red] ', stream_get_contents($red_output->getStream()));
	}

	public function testDefaultWorksWithZero() {
		$dialog = new DialogHelper();
		$dialog->setInputStream($this->getInputStream("0\n\n"));
		$this->assertEquals('0', $dialog->ask($this->getOutputStream(), 'Pick a number ', '4'));
		$this->assertEquals('0', $dialog->ask($output = $this->getOutputStream(), 'Pick a number ', true));
		rewind($output->getStream());
		$this->assertEquals('Pick a number [Default: 0] ', stream_get_contents($output->getStream()));
	}

}

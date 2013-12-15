<?php

namespace Neptune\Console;

/**
 * DialogHelperTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DialogHelperTest extends \PHPUnit_Framework_TestCase {

	public function setup() {

	}

	public function tearDown() {

	}

	public function testNoDefault() {
		/* $this->c->setDefaultOption('Pick a colour', 'black'); */
		/* $this->assertEquals('Pick a colour', $this->c->addDefaultToPrompt('Pick a colour', null)); */
		/* $this->assertEquals('Pick a colour', $this->c->addDefaultToPrompt('Pick a colour', "")); */
	}

	/* public function testNamedDefault() { */
	/* 	$this->c->setDefaultOption('Pick a colour', 'black'); */
	/* 	$this->assertEquals('Pick a colour (Default: white)', $this->c->addDefaultToPrompt('Pick a colour', 'white')); */
	/* } */

	/* public function testDefaultWorksWithZero() { */
	/* 	$this->assertEquals('Enter a number (Default: 0)', $this->c->addDefaultToPrompt('Enter a number', 0)); */
	/* 	$this->assertEquals('Enter a number (Default: 0)', $this->c->addDefaultToPrompt('Enter a number', '0')); */
	/* } */

	/* public function testLastDefault() { */
	/* 	$this->c->setDefaultOption('Pick a colour', 'black'); */
	/* 	$this->assertEquals('Pick a colour (Default: black)', $this->c->addDefaultToPrompt('Pick a colour', true)); */
	/* } */

	/* public function testOptions() { */
	/* 	$expected = 'Pick a colour [0:black, 1:white] '; */
	/* 	$actual = $this->c->options(array('black', 'white'), 'Pick a colour'); */
	/* 	$this->assertEquals($expected, $actual); */
	/* 	$expected ='Pick a colour [0:black, 1:white, 2:red, 3:yellow, 4:green] '; */
	/* 	$actual = $this->c->options(array('black', 'white', 'red', 'yellow', 'green'), */
	/* 						  'Pick a colour'); */
	/* 	$this->assertEquals($expected, $actual); */
	/* } */

	/* public function testOptionsEmptyArray() { */
	/* 	$this->setExpectedException('\\Neptune\\Console\\ConsoleException'); */
	/* 	$this->c->options(array()); */
	/* } */


}

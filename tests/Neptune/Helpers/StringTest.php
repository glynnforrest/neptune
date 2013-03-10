<?php

namespace Neptune\Helpers;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

use Neptune\Validate\Validator;

class StringTest extends \PHPUnit_Framework_TestCase {

	public function testSpaces() {
		$this->assertEquals('var name', String::spaces('var_name'));
		$this->assertEquals('var name', String::spaces('var-name'));
	}

	public function testSlugify() {
		$this->assertEquals('one-two-three-four', String::slugify('one two three four'));
		$this->assertEquals('alpha-bet-soup', String::slugify('Alpha_Bet_Soup'));
		$this->assertEquals('url-with-characters-removed', String::slugify('Url with??> characters_removed;;\';;'));
	}

	public function testCamelCase() {
		$this->assertEquals('ClassName', String::camelCase('class name'));
		$this->assertEquals('ClassName', String::camelCase('class-name'));
		$this->assertEquals('ClassName', String::camelCase('class_name'));
		$this->assertEquals('functionThatDoesStuff', String::camelCase('function that does%^ stuff', false));
	}

	public function testRandomLength() {
		$this->assertEquals(8, strlen(String::random(8)));
		$this->assertEquals(0, strlen(String::random(0)));
		$this->assertEquals(0, strlen(String::random(-3)));
	}

	public function testRandomAlpha() {
		$v = new Validator(array('str' => String::random(10)));
		$v->check('str', 'alpha');
		$this->assertTrue($v->validate());
		$v = new Validator(array('str' => String::random(10, String::ALPHA)));
		$v->check('str', 'alpha');
		$this->assertTrue($v->validate());
	}

	public function testRandomAlphanum() {
		$v = new Validator(array('str' => String::random(10, String::ALPHANUM)));
		$v->check('str', 'alphanum');
		$this->assertTrue($v->validate());
	}

	public function testRandomNum() {
		$v = new Validator(array('str' => String::random(10, String::NUM)));
		$v->check('str', 'num');
		$this->assertTrue($v->validate());
	}

	public function testRandomHex() {
		$v = new Validator(array('str' => String::random(10, String::HEX)));
		$v->check('str', 'hex');
		$this->assertTrue($v->validate());
	}

	public function testPlural() {
		$this->assertEquals('cats', String::plural('cat'));
		$this->assertEquals('mice', String::plural('mouse'));
		$this->assertEquals('lice', String::plural('louse'));
		$this->assertEquals('houses', String::plural('house'));
		$this->assertEquals('buses', String::plural('bus'));
		$this->assertEquals('sheep', String::plural('sheep'));
		$this->assertEquals('cacti', String::plural('cactus'));
		$this->assertEquals('octopi', String::plural('octopus'));
		$this->assertEquals('fish', String::plural('fish'));
		$this->assertEquals('wishes', String::plural('wish'));
		$this->assertEquals('boxes', String::plural('box'));
		$this->assertEquals('witches', String::plural('witch'));
		$this->assertEquals('jeans', String::plural('jeans'));
		$this->assertEquals('scissors', String::plural('scissors'));
		$this->assertEquals('curries', String::plural('curry'));
		$this->assertEquals('flies', String::plural('fly'));
		$this->assertEquals('pringles', String::plural('pringle'));
		$this->assertEquals('tomatoes', String::plural('tomato'));
		$this->assertEquals('monkeys', String::plural('monkey'));
		$this->assertEquals('shoes', String::plural('shoe'));
		$this->assertEquals('messages', String::plural('message'));
	}

	public function testSingle() {
		$this->assertEquals('cat', String::single('cats'));
		$this->assertEquals('mouse', String::single('mice'));
		$this->assertEquals('louse', String::single('lice'));
		$this->assertEquals('house', String::single('houses'));
		$this->assertEquals('bus', String::single('buses'));
		$this->assertEquals('sheep', String::single('sheep'));
		$this->assertEquals('cactus', String::single('cacti'));
		$this->assertEquals('octopus', String::single('octopi'));
		$this->assertEquals('fish', String::single('fish'));
		$this->assertEquals('wish', String::single('wishes'));
		$this->assertEquals('box', String::single('boxes'));
		$this->assertEquals('witch', String::single('witches'));
		$this->assertEquals('jeans', String::single('jeans'));
		$this->assertEquals('scissors', String::single('scissors'));
		$this->assertEquals('curry', String::single('curries'));
		$this->assertEquals('fly', String::single('flies'));
		$this->assertEquals('pringle', String::single('pringles'));
		$this->assertEquals('tomato', String::single('tomatoes'));
		$this->assertEquals('monkey', String::single('monkeys'));
		$this->assertEquals('shoe', String::single('shoes'));
		$this->assertEquals('message', String::single('messages'));
	}

	public function testJoinList() {
		$this->assertEquals('one, two, three',
							String::joinList(array('one', 'two', 'three')));
		$this->assertEquals('one',
							String::joinList(array('one')));
		$this->assertEquals('one | two | three | four',
							String::joinList(array('one', 'two', 'three', 'four'), ' | '));
		$this->assertEquals('number one, number two',
							String::joinList(array('one', 'two'), ', ', 'number '));
		$this->assertEquals('`one`, `two`',
							String::joinList(array('one', 'two'), ', ', '`', '`'));
	}


}

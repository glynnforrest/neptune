<?php

namespace Neptune\Tests\Form;

use Neptune\Form\FormRow;
use Neptune\Helpers\Html;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * FormRowTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FormRowTest extends \PHPUnit_Framework_TestCase {

	public function testSimpleRow() {
		$r = new FormRow('text', 'name');
		$expected = Html::tag('label', 'Name', array('for' => 'name', 'id' => 'name'));
		$expected .= Html::input('text', 'name');
		$this->assertEquals($expected, $r->render());
	}

	public function testRowWithValue() {
		$email = 'test@example.com';
		$r = new FormRow('text', 'email', $email);
		$expected = Html::tag('label', 'Email', array('for' => 'email', 'id' => 'email'));
		$expected .= Html::input('text', 'email', $email);
		$this->assertEquals($expected, $r->render());
	}

	public function testError() {
		$r = new FormRow('text', 'message');
		$error = 'Message field is invalid.';
		$r->setError($error);
		$this->assertEquals($error, $r->getError());
	}

	public function testErrorHtml() {
		$r = new FormRow('text', 'email');
		$error = 'Email field is invalid.';
		$r->setError($error);
		$this->assertEquals(Html::tag('p', $error), $r->error());
	}

	public function testNoErrorHtml() {
		$r = new FormRow('text', 'email');
		$this->assertNull($r->error());
	}

	public function testRowWithValueAndError() {
		$password = 'super_secret';
		$r = new FormRow('password', 'password', $password);
		$password_error = 'Password is incorrect.';
		$r->setError($password_error);
		$expected = Html::tag('label', 'Password', array('for' => 'password', 'id' => 'password'));
		$expected .= Html::input('password', 'password', $password);
		$expected .= Html::tag('p', $password_error);
		$this->assertEquals($expected, $r->render());
	}

}

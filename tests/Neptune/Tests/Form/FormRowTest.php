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
		$expected = Html::label('name', 'Name');
		$expected .= Html::input('text', 'name');
		$this->assertSame($expected, $r->render());
	}

	public function testRowWithValue() {
		$email = 'test@example.com';
		$r = new FormRow('text', 'email', $email);
		$expected = Html::label('email', 'Email');
		$expected .= Html::input('text', 'email', $email);
		$this->assertSame($expected, $r->render());
	}

	public function testError() {
		$r = new FormRow('text', 'message');
		$error = 'Message field is invalid.';
		$r->setError($error);
		$this->assertSame($error, $r->getError());
	}

	public function testErrorHtml() {
		$r = new FormRow('text', 'email');
		$error = 'Email field is invalid.';
		$r->setError($error);
		$this->assertSame(Html::tag('p', $error), $r->error());
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
		$expected = Html::label('password', 'Password');
		$expected .= Html::input('password', 'password', $password);
		$expected .= Html::tag('p', $password_error);
		$this->assertSame($expected, $r->render());
	}

	public function testHiddenInput() {
		$r = new FormRow('hidden', 'secret_value', '12345');
		$expected = Html::input('hidden', 'secret_value', '12345');
		$this->assertSame($expected, $r->input());
		$this->assertSame($expected, $r->render());
	}

	public function testSubmitInputAutoValue() {
		$r = new FormRow('submit', 'submit-form');
		//Form Row should add a title to the submit button
		$expected = Html::input('submit', 'submit-form', 'Submit form');
		$this->assertSame($expected, $r->input());
		//update this after row_html is implemented
		$this->assertSame($expected, $r->render());
	}

	public function testSubmitInputOverrideValue() {
		$r = new FormRow('submit', 'submit');
		$expected = Html::input('submit', 'submit', 'Submit');
		$this->assertSame($expected, $r->input());
		//change the text on the button
		$r->setValue('Send the form!');
		$expected = Html::input('submit', 'submit', 'Send the form!');
		$this->assertSame($expected, $r->input());
	}

	public function testSetValue() {
		$r = new FormRow('text', 'username');
		$r->setValue('user1');
		$this->assertSame('user1', $r->getValue());
		$expected = Html::input('text', 'username', 'user1');
		$this->assertSame($expected, $r->input());
	}

	public function testGetAndSetType() {
		$r = new FormRow('text', 'username');
		$this->assertSame('text', $r->getType());
		$r->setType('password');
		$this->assertSame('password', $r->getType());
	}

	public function setChangeType() {
		$r = new FormRow('text', 'pass');
		$this->assertSame(Html::input('text', 'pass', 'secret'), $f->input());
		$r->setType('password');
		$this->assertSame(Html::input('password', 'pass'), $f->input());
	}

	public function testSensibleLabelString() {
		$labels = array (
			'password' => 'Password',
			'user-id' => 'User id',
			'EmailAddress' => 'Email address',
			'date_format' => 'Date format',
			'_save' => 'Save'
		);
		foreach ($labels as $name => $label) {
			$r = new FormRow('text', $name);
			$this->assertSame($label, $r->getLabel());
		}
	}

	public function testInvalidInputTypeThrowsException() {

	}

}
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

	public function testCheckboxUnchecked() {
		$r = new FormRow('checkbox', 'remember-me');
		//the checkbox will have the value of 'checked' always
		$html = Html::input('checkbox', 'remember-me', 'checked');
		$this->assertSame($html, $r->input());
	}

	public function testCheckboxChecked() {
		$r = new FormRow('checkbox', 'remember-me', 'some-truthy-value');
		$html = Html::input('checkbox', 'remember-me', 'checked', array('checked'));
		$this->assertSame($html, $r->input());
	}

	public function testCheckboxSetChecked() {
		$r = new FormRow('checkbox', 'remember-me');
		$r->setValue('some-truthy-value');
		$html = Html::input('checkbox', 'remember-me', 'checked', array('checked'));
		$this->assertSame($html, $r->input());
	}

	public function testCheckboxSetUnchecked() {
		$r = new FormRow('checkbox', 'remember-me', 'some-truthy-value');
		$r->setValue(null);
		$html = Html::input('checkbox', 'remember-me', 'checked');
		$this->assertSame($html, $r->input());
	}

	public function testSetAndGetOptions() {
		$r = new FormRow('text', 'username', null, array('id' => 'username-input'));
		$this->assertSame(array('id' => 'username-input'), $r->getOptions());
		$html = Html::input('text', 'username', null, array('id' => 'username-input'));
		$this->assertSame($html, $r->input());

		$this->assertInstanceOf('\Neptune\Form\FormRow', $r->setOptions(array('class' => 'input')));
		$this->assertSame(array('class' => 'input'), $r->getOptions());
		$html = Html::input('text', 'username', null, array('class' => 'input'));
		$this->assertSame($html, $r->input());
	}

	public function testAddOptions() {
		$r = new FormRow('text', 'username');
		$this->assertSame(array(), $r->getOptions());

		$this->assertInstanceOf('\Neptune\Form\FormRow', $r->addOptions(array('id' => 'username-input')));
		$this->assertSame(array('id' => 'username-input'), $r->getOptions());
		$html = Html::input('text', 'username', null, array('id' => 'username-input'));
		$this->assertSame($html, $r->input());

		$this->assertInstanceOf('\Neptune\Form\FormRow', $r->addOptions(array('class' => 'input')));
		$expected_options = array(
			'id' => 'username-input',
			'class' => 'input'
		);
		$this->assertSame($expected_options, $r->getOptions());
		$html = Html::input('text', 'username', null, $expected_options);
		$this->assertSame($html, $r->input());
	}

	public function testCheckboxPlusAddOptions() {
		$r = new FormRow('checkbox', 'remember-me', 'yes');
		$r->addOptions(array('id' => 'checkbox-id'));
		$html = Html::input('checkbox', 'remember-me', 'checked', array('checked', 'id' => 'checkbox-id'));
		$this->assertSame($html, $r->input());
	}

	public function testCheckboxPlusSetOptions() {
		$r = new FormRow('checkbox', 'remember-me', 'yes');
		$r->setOptions(array('id' => 'checkbox-id'));
		$html = Html::input('checkbox', 'remember-me', 'checked', array('checked', 'id' => 'checkbox-id'));
		$this->assertSame($html, $r->input());
	}

	public function testCheckboxSetCheckedPreserveOptions() {
		$r = new FormRow('checkbox', 'remember-me');
		$r->setOptions(array('class' => 'checkbox'));
		$this->assertSame(array('class' => 'checkbox'), $r->getOptions());
		$r->setValue('yes');
		$this->assertSame(array('class' => 'checkbox'), $r->getOptions());

		$html = Html::input('checkbox', 'remember-me', 'checked', array('class' => 'checkbox', 'checked'));
		$this->assertSame($html, $r->input());
	}

	public function testCheckboxSetUncheckedPreserveOptions() {
		$r = new FormRow('checkbox', 'remember-me', 'yes');
		$r->setOptions(array('class' => 'checkbox'));
		$this->assertSame(array('class' => 'checkbox'), $r->getOptions());
		$r->setValue(null);
		$this->assertSame(array('class' => 'checkbox'), $r->getOptions());

		$html = Html::input('checkbox', 'remember-me', 'checked', array('class' => 'checkbox'));
		$this->assertSame($html, $r->input());
	}

	public function testStillGetCheckboxValueAfterRender() {
		$r = new FormRow('checkbox', 'remember-me', 'yes');
		$html = Html::label('remember-me', 'Remember me');
		$html .= Html::input('checkbox', 'remember-me', 'checked', array('checked'));
		$this->assertSame($html, $r->render());
		$this->assertSame('yes', $r->getValue());
	}

	public function testInvalidInputTypeThrowsException() {

	}

}

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

	public function testGetAndSetValue() {
		$r = new FormRow('text', 'username');
		$this->assertSame(null, $r->getValue());
		$this->assertInstanceOf('\Neptune\Form\FormRow', $r->setValue('user1'));
		$this->assertSame('user1', $r->getValue());
	}

	public function testGetAndSetLabel() {
		$r = new FormRow('text', 'password');
		$this->assertSame('Password', $r->getLabel());
		$this->assertInstanceOf('\Neptune\Form\FormRow', $r->setLabel('pass-word'));
		$this->assertSame('pass-word', $r->getLabel());
	}

	public function sensibleLabelStringProvider() {
		return array(
			array('password', 'Password'),
			array('user-id', 'User id'),
			array('EmailAddress', 'Email address'),
			array('date_format', 'Date format'),
			array('_save', 'Save')
		);
	}

	/**
	 * @dataProvider sensibleLabelStringProvider()
	 *
	 * A sensible string is applied to the label on instantiation, but
	 * not on calling setLabel().
	 */
	public function testSensibleLabelString($name, $label) {
		$r = new FormRow('text', $name);
		$this->assertSame($label, $r->getLabel());
		$this->assertInstanceOf('\Neptune\Form\FormRow', $r->setLabel($name));
		$this->assertSame($name, $r->getLabel());
	}

	public function testLabelHtml() {
		$r = new FormRow('password', 'pass-word');
		$html = Html::label('pass-word', 'Pass word');
		$this->assertSame($html, $r->label());
	}

	public function testGetAndSetType() {
		$r = new FormRow('text', 'username');
		$this->assertSame('text', $r->getType());
		$this->assertInstanceOf('\Neptune\Form\FormRow', $r->setType('password'));
		$this->assertSame('password', $r->getType());
	}

	public function testChangeTypeHtml() {
		$r = new FormRow('text', 'pass', 'secret');
		$this->assertSame(Html::input('text', 'pass', 'secret'), $r->input());
		$this->assertInstanceOf('\Neptune\Form\FormRow', $r->setType('password'));
		$this->assertSame(Html::input('password', 'pass'), $r->input());
	}

	public function testGetAndSetError() {
		$r = new FormRow('text', 'username');
		$this->assertNull($r->getError());
		$msg = 'Username is required.';
		$this->assertInstanceOf('\Neptune\Form\FormRow', $r->setError($msg));
		$this->assertSame($msg, $r->getError());
	}

    public function testErrorHtml()
    {
        $r = new FormRow('text', 'username');
        $this->assertNull($r->getError());
        $msg = 'Username is required.';
        $this->assertInstanceOf('\Neptune\Form\FormRow', $r->setError($msg));
        $html = '<small class="error">Username is required.</small>';
        $this->assertSame($html, $r->error());
    }

	public function testGetAndSetOptions() {
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

	public function testGetAndSetChoices() {
		$r = new FormRow('radio', 'gender');
		$this->assertInstanceOf('\Neptune\Form\FormRow',
		$r->setChoices(array('male', 'female')));
		$this->assertSame(array('Male' => 'male', 'Female' => 'female'),
		$r->getChoices());

		$this->assertInstanceOf('\Neptune\Form\FormRow', $r->setChoices(array()));
		$this->assertSame(array(), $r->getChoices());
	}

	public function testSetChoicesAddsSensibleLabels() {
		$r = new FormRow('select', 'variables');
		$this->assertInstanceOf('\Neptune\Form\FormRow',
		$r->setChoices(array('first_name', 'last_name')));

		$nice_array = array('First name' => 'first_name', 'Last name' => 'last_name');
		$this->assertSame($nice_array, $r->getChoices());

		$html = Html::select('variables', $nice_array);
		$this->assertSame($html, $r->input());
	}

	public function testSetChoicesDoesNotChangeFloatKeys() {
		$r = new FormRow('select', 'var');
		$this->assertInstanceOf('\Neptune\Form\FormRow',
		$r->setChoices(array('0.1' => 'foo', '0.2' => 'bar')));
		$this->assertSame(array('0.1' => 'foo', '0.2' => 'bar'),
		$r->getChoices());
	}

	public function testSetChoicesInvalidTypeThrowsException() {
		$r = new FormRow('text', 'username');
		$error = "Form row 'username' with type 'text' does not support choices";
		$this->setExpectedException('\Exception', $error);
		$r->setChoices(array());
	}

    public function testAddChoicesInvalidTypeThrowsException()
    {
        $r = new FormRow('text', 'username');
        $error = "Form row 'username' with type 'text' does not support choices";
        $this->setExpectedException('\Exception', $error);
        $r->addChoices(array());
    }

	public function testAddChoices() {
		$r = new FormRow('radio', 'decision');
		$r->setChoices(array('yes', 'no'));
		$this->assertInstanceOf('\Neptune\Form\FormRow',
		$r->addChoices(array('maybe')));
		$this->assertSame(array('Yes' => 'yes', 'No' => 'no', 'Maybe' => 'maybe'),
		$r->getChoices());
	}

    public function testInvalidInputTypeThrowsException()
    {
        $error = 'Neptune\Form\FormRow does not support type "strange"';
        $this->setExpectedException('\Exception', $error);
        $r = new FormRow('strange', 'foo');
    }

}

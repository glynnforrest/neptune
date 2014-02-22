<?php

namespace Neptune\Tests\Form\FormRow;

use Neptune\Form\FormRow;
use Neptune\Helpers\Html;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * FormRowTextTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FormRowTextTest extends \PHPUnit_Framework_TestCase {

	public function testConstruct() {
		$r = new FormRow('text', 'username');
		$this->assertSame('text', $r->getType());
	}

	public function testInput() {
		$r = new FormRow('text', 'name');
		$expected = Html::input('text', 'name');
		$this->assertSame($expected, $r->input());
	}

	public function testRow() {
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

	public function testRowWithError() {
		$r = new FormRow('text', 'email');
		$error = 'Email is incorrect.';
		$r->setError($error);
		$expected = Html::label('email', 'Email');
		$expected .= Html::input('text', 'email');
		$expected .= '<small class="error">' . $error . '</small>';
		$this->assertSame($expected, $r->render());
	}

	public function testRowWithValueAndError() {
		$email = 'foo_bar';
		$r = new FormRow('text', 'email', $email);
		$error = 'Email is invalid.';
		$r->setError($error);
		$expected = Html::label('email', 'Email');
		$expected .= Html::input('text', 'email', $email);
		$expected .= '<small class="error">' . $error . '</small>';
		$this->assertSame($expected, $r->render());
	}

}

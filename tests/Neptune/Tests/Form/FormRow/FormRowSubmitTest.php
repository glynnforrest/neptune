<?php

namespace Neptune\Tests\Form\FormRow;

use Neptune\Form\FormRow;
use Neptune\Helpers\Html;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * FormRowSubmitTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FormRowSubmitTest extends \PHPUnit_Framework_TestCase {

	public function testConstruct() {
		$r = new FormRow('submit', 'save');
		$this->assertSame('submit', $r->getType());
	}

	public function testInput() {
		$r = new FormRow('submit', 'submit-form');
		//Form Row should add a sensible title to the submit button
		$expected = Html::input('submit', 'submit-form', 'Submit form');
		$this->assertSame($expected, $r->input());
	}

	public function testInputValueCanBeOverridden() {
		$r = new FormRow('submit', 'submit-form');
		$r->setValue('SAVE');
		$expected = Html::input('submit', 'submit-form', 'SAVE');
		$this->assertSame($expected, $r->input());
	}

	public function testRow() {
		$r = new FormRow('submit', '_save');
		$expected = Html::input('submit', '_save', 'Save');
		//update this after row_html is implemented
		$this->assertSame($expected, $r->render());
	}

	public function testRowWithValue() {
		$r = new FormRow('submit', '_save', 'GO');
		$expected = Html::input('submit', '_save', 'GO');
		$this->assertSame($expected, $r->render());
	}

	public function testRowWithError() {
		$r = new FormRow('submit', '_save');
		$r->setError('An error occurred.');
		$expected = Html::input('submit', '_save', 'Save');
		$this->assertSame($expected, $r->render());
	}

	public function testRowWithValueAndError() {
		$r = new FormRow('submit', '_save', 'SEND');
		$r->setError('An error occurred.');
		$expected = Html::input('submit', '_save', 'SEND');
		$this->assertSame($expected, $r->render());
	}

}

<?php

namespace Neptune\Tests\Form\FormRow;

use Neptune\Form\FormRow;
use Neptune\Helpers\Html;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * FormRowSelectTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FormRowSelectTest extends \PHPUnit_Framework_TestCase {

	public function testConstruct() {
		$r = new FormRow('select', 'choices');
		$this->assertSame('select', $r->getType());
	}

	public function testInput() {
		$r = new FormRow('select', 'decision');
		$html = Html::select('decision', array());
		$this->assertSame($html, $r->input());
	}

	public function testInputWithChoices() {
		$r = new FormRow('select', 'decision');
		$r->setChoices(array('Yes' => 'yes', 'No' => 'no'));
		$html = Html::select('decision', array('Yes' => 'yes', 'No' => 'no'));
		$this->assertSame($html, $r->input());
	}

	public function testInputWithValue() {
		$r = new FormRow('select', 'decision', 'yes');
		$html = Html::select('decision', array());
		$this->assertSame($html, $r->input());
	}

	public function testInputWithValueAndChoices() {
		$r = new FormRow('select', 'decision');
		$r->setChoices(array('Yes' => 'yes', 'No' => 'no'));
		$r->setValue('no');
		$html = Html::select('decision', array('Yes' => 'yes', 'No' => 'no'), 'no');
		$this->assertSame($html, $r->input());
	}

	public function testInputWithStrangeTypes() {
		$r = new FormRow('select', 'decision');
		$choices = array(1.1 => 1, 2 => 2, '3' => 3, 4);
		$r->setChoices($choices);
		$html = Html::select('decision', $choices);
		$this->assertSame($html, $r->input());
	}

	public function testRow() {
		$r = new FormRow('select', 'decision');
		$r->setChoices(array('yes', 'no'));
		$expected = Html::label('decision', 'Decision');
		$expected .= Html::select('decision', array('Yes' => 'yes', 'No' => 'no'));
		$this->assertSame($expected, $r->render());
	}

	public function testRowWithValue() {
		$r = new FormRow('select', 'decision', 'yes');
		$r->setChoices(array('yes', 'no'));
		$expected = Html::label('decision', 'Decision');
		$expected .= Html::select('decision', array('Yes' => 'yes', 'No' => 'no'), 'yes');
		$this->assertSame($expected, $r->render());
	}

	public function testRowWithError() {
		$r = new FormRow('select', 'decision');
		$error = 'No choice given.';
		$r->setError($error);
		$expected = Html::label('decision', 'Decision');
		$expected .= Html::select('decision', array());
		$expected .= '<small class="error">' . $error . '</small>';
		$this->assertSame($expected, $r->render());
	}

	public function testRowWithValueAndError() {
		$r = new FormRow('select', 'decision', 'no');
		$error = 'Bad move, pal.';
		$r->setError($error);
		$r->setChoices(array('yes', 'no'));
		$expected = Html::label('decision', 'Decision');
		$expected .= Html::select('decision', array('Yes' => 'yes', 'No' => 'no'), 'no');
		$expected .= '<small class="error">' . $error . '</small>';
		$this->assertSame($expected, $r->render());
	}

}

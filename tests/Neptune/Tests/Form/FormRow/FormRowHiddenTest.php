<?php

namespace Neptune\Tests\Form\FormRow;

use Neptune\Form\FormRow;
use Neptune\Helpers\Html;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * FormRowHiddenTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FormRowHiddenTest extends \PHPUnit_Framework_TestCase {

	public function testConstruct() {
		$r = new FormRow('hidden', 'token');
		$this->assertSame('hidden', $r->getType());
	}

	public function testInput() {
		$r = new FormRow('hidden', 'token');
		$expected = Html::input('hidden', 'token');
		$this->assertSame($expected, $r->input());
	}

	public function testRow() {
		$r = new FormRow('hidden', 'token');
		$expected = Html::input('hidden', 'token');
		$this->assertSame($expected, $r->render());
	}

	public function testRowWithValue() {
		$token = '12345';
		$r = new FormRow('hidden', 'token', $token);
		$expected = Html::input('hidden', 'token', $token);
		$this->assertSame($expected, $r->render());
	}

	public function testRowWithError() {
		$r = new FormRow('hidden', 'token');
		$r->setError('Token is invalid.');
		$expected = Html::input('hidden', 'token');
		$this->assertSame($expected, $r->render());
	}

	public function testRowWithValueAndError() {
		$token = '123456789';
		$r = new FormRow('hidden', 'token', $token);
		$r->setError('Token is invalid');
		$expected = Html::input('hidden', 'token', $token);
		$this->assertSame($expected, $r->render());
	}

}

<?php

namespace Neptune\Tests\Form\FormRow;

use Neptune\Form\FormRow;
use Neptune\Helpers\Html;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * FormRowTextareaTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FormRowTextareaTest extends \PHPUnit_Framework_TestCase {

	public function testConstruct() {
		$r = new FormRow('textarea', 'username');
		$this->assertSame('textarea', $r->getType());
	}

	public function testInput() {
		$r = new FormRow('textarea', 'name');
		$expected = Html::input('textarea', 'name');
		$this->assertSame($expected, $r->input());
	}

	public function testRow() {
		$r = new FormRow('textarea', 'name');
		$expected = Html::label('name', 'Name');
		$expected .= Html::input('textarea', 'name');
		$this->assertSame($expected, $r->render());
	}

	public function testRowWithValue() {
		$comment = 'Hello world';
		$r = new FormRow('textarea', 'comment', $comment);
		$expected = Html::label('comment', 'Comment');
		$expected .= Html::input('textarea', 'comment', $comment);
		$this->assertSame($expected, $r->render());
	}

	public function testRowWithError() {
		$r = new FormRow('textarea', 'comment');
		$error = 'Comment is required.';
		$r->setError($error);
		$expected = Html::label('comment', 'Comment');
		$expected .= Html::input('textarea', 'comment');
		$expected .= Html::tag('p', $error);
		$this->assertSame($expected, $r->render());
	}

	public function testRowWithValueAndError() {
		$comment = 'Hello world';
		$r = new FormRow('textarea', 'comment', $comment);
		$error = 'Comment isn\'t good enough.';
		$r->setError($error);
		$expected = Html::label('comment', 'Comment');
		$expected .= Html::input('textarea', 'comment', $comment);
		$expected .= Html::tag('p', $error);
		$this->assertSame($expected, $r->render());
	}

}

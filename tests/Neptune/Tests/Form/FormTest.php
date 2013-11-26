<?php

namespace Neptune\Tests\Form;

use Neptune\Form\Form;
use Neptune\Helpers\Html;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * FormTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class FormTest extends \PHPUnit_Framework_TestCase {

	public function testCreateEmptyForm() {
		$f = new Form('/post/url');
		$expected = Html::tag('form', null, array('action' => '/post/url', 'method' => 'POST'));
		$this->assertSame($expected, $f->render());
	}

	public function testCreateSimpleForm() {
		$f = new Form('/post/url', 'get');
		$f->text('name');
		$expected = Html::openTag('form', array('action' => '/post/url', 'method' => 'GET'));
		$expected .= Html::label('name', 'Name');
		$expected .= Html::input('text', 'name');
		$expected .= '</form>';
		$this->assertSame($expected, $f->render());
	}

	public function testGetAndSetAction() {
		$f = new Form('/login');
		$this->assertSame('/login', $f->getAction());
		$f->setAction('/login/somewhere/else');
		$this->assertSame('/login/somewhere/else', $f->getAction());
	}

	public function testCreateFormNoAction() {
		$_SERVER['REQUEST_URI'] = '/some/url';
		$f = new Form();
		$this->assertSame('/some/url', $f->getAction());
	}

	public function testGetAndSetMethod() {
		$f = new Form();
		$this->assertSame('POST', $f->getMethod());
		$f->setMethod('get');
		$this->assertSame('GET', $f->getMethod());
	}

	public function testSetMethodThrowsException() {
		$f = new Form();
		$this->setExpectedException('\Exception');
		$f->setMethod('something-stupid');
	}

	public function testGetAndSetOptions() {
		$f = new Form();
		$this->assertSame(array(), $f->getOptions());
		$options = array('id' => 'my-form', 'class' => 'form');
		$f->setOptions($options);
		$this->assertSame($options, $f->getOptions());
	}

	public function testSetAndGet() {
		$f = new Form();
		$f->text('message');
		$this->assertSame(null, $f->get('message'));
		$f->set('message', 'hello');
		$this->assertSame('hello', $f->get('message'));
	}

	public function testSetThrowsException() {
		$f = new Form();
		$this->setExpectedException('\Exception');
		$f->set('username', 'user42');
	}

	public function testSetCreateNewRow() {
		$f = new Form();
		$f->set('username', 'user42', true);
		$this->assertSame('user42', $f->get('username'));
	}

	public function testGetRow() {
		$f = new Form();
		$f->text('username');
		$this->assertInstanceOf('\Neptune\Form\FormRow', $f->getRow('username'));
	}

	public function testRowIsReturnedByReference() {
		$f = new Form();
		$f->text('username');
		//check that the same FormRow instance is returned every time.
		$first = $f->getRow('username');
		$this->assertNull($first->getValue());
		$second = $f->getRow('username');
		$second->setValue('user');
		$this->assertSame('user', $first->getValue());
		$this->assertSame($first, $second);
	}

	public function testText() {
		$f = new Form();
		$f->text('username');
		$r = $f->getRow('username');
		$this->assertSame('text', $r->getType());
		$expected = Html::input('text', 'username');
		$this->assertSame($expected, $r->input());
	}

	public function testPassword() {
		$f = new Form();
		$f->password('secret');
		$r = $f->getRow('secret');
		$this->assertSame('password', $r->getType());
		$expected = Html::input('password', 'secret');
		$this->assertSame($expected, $r->input());
	}

	public function testTextarea() {
		$f = new Form();
		$f->textarea('comment', 'Some comment');
		$r = $f->getRow('comment');
		$this->assertSame('textarea', $r->getType());
		$expected = Html::input('textarea', 'comment', 'Some comment');
		$this->assertSame($expected, $r->input());
	}

	public function testInput() {
		$f = new Form();
		$f->text('name');
		$this->assertSame(Html::input('text', 'name'), $f->input('name'));
	}

	public function testLabel() {
		$f = new Form();
		$f->text('username');
		$this->assertSame(Html::label('username', 'Username'), $f->label('username'));
	}

	public function testError() {
		$f = new Form();
		$f->text('email');
		$this->assertNull($f->error('email'));
		$error_msg = 'Email is invalid.';
		$f->getRow('email')->setError($error_msg);
		$this->assertSame(Html::tag('p', $error_msg), $f->error('email'));
	}

	public function testGetAndSetValues() {
		$f = new Form();
		$f->text('username', 'glynn');
		$f->password('password', 'secret');
		$expected = array('username' => 'glynn', 'password' => 'secret');
		$this->assertSame($expected, $f->getValues());
		$changed = array('username' => 'glynnforrest', 'password' => 'token');
		$f->setValues($changed);
		$this->assertSame($changed, $f->getValues());
	}

	public function testSetValuesThrowsException() {
		$f = new Form();
		$f->text('username', 'glynn');
		$f->password('password', 'secret');
		$expected = array('username' => 'glynn', 'password' => 'secret', 'foo' => 'bar');
		$this->setExpectedException('\Exception');
		$f->setValues($expected);
	}

	public function testCreateFromArray() {
		$f = new Form('/url');
		$values = array('username' => 'glynn', 'age' => 100);
		$f->setValues($values, true);
		$expected = Html::openTag('form', array('action' => '/url', 'method' => 'POST'));
		$expected .= Html::label('username', 'Username');
		$expected .= Html::input('text', 'username', 'glynn');
		$expected .= Html::label('age', 'Age');
		$expected .= Html::input('text', 'age', 100);
		$expected .= '</form>';
		$this->assertSame($expected, $f->render());
	}

	public function testCreateAndModify() {
		$f = new Form('/url');
		$f->text('username', 'glynn');
		$comment =  'Hello world';
		$f->text('comment', $comment);
		$first_form = Html::openTag('form', array('action' => '/url', 'method' => 'POST'));
		$first_form .= Html::label('username', 'Username');
		$first_form .= Html::input('text', 'username', 'glynn');
		$first_form .= Html::label('comment', 'Comment');
		$first_form .= Html::input('text', 'comment', $comment);
		$first_form .= '</form>';
		$this->assertSame($first_form, $f->render());

		//now modify the rows
		$username_row = $f->getRow('username');
		$username_row->setValue('glynnforrest');

		$comment_row = $f->getRow('comment');
		$comment_row->setType('textarea');

		$second_form = Html::openTag('form', array('action' => '/url', 'method' => 'POST'));
		$second_form .= Html::label('username', 'Username');
		$second_form .= Html::input('text', 'username', 'glynnforrest');
		$second_form .= Html::label('comment', 'Comment');
		$second_form .= Html::input('textarea', 'comment', $comment);
		$second_form .= '</form>';
		$this->assertSame($second_form, $f->render());
	}

	public function testToStringCallsRender() {
		$f = new Form();
		$this->assertSame($f->render(), $f->__toString());
	}

	public function testAddErrors() {
		$f = new Form('/url');
		$f->text('username');
		$f->text('email', 'foo');

		$username_error = 'Username is required.';
		$email_error = 'Email is invalid';
		$f->addErrors(array(
			'username' => $username_error,
			'email' => $email_error
		));

		//test the error messages are stored in each FormRow instance
		$this->assertSame($username_error, $f->getRow('username')->getError());
		$this->assertSame($email_error, $f->getRow('email')->getError());

		//test the error html is rendered
		$username_error_html = Html::tag('p', $username_error);
		$this->assertSame($username_error_html, $f->getRow('username')->error());
		$email_error_html = Html::tag('p', $email_error);
		$this->assertSame($email_error_html, $f->getRow('email')->error());

		//test the completed form contains the errors
		$form = Html::openTag('form', array('action' => '/url', 'method' => 'POST'));
		$form .= Html::label('username', 'Username');
		$form .= Html::input('text', 'username');
		$form .= $username_error_html;
		$form .= Html::label('email', 'Email');
		$form .= Html::input('text', 'email', 'foo');
		$form .= $email_error_html;
		$form .= '</form>';
		$this->assertSame($form, $f->render());
	}

}

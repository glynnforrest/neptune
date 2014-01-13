<?php

namespace Neptune\Tests\Routing;

use Neptune\Controller\Controller;

/**
 * TestController
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TestController extends Controller {

	public function indexAction() {
		return 'test route';
	}

	public function withEchoAction() {
		echo 'hello from echo';
		return 'return value';
	}

	public function echoAction() {
		echo 'testing';
	}

	public function nothingAction() {
		//do nothing
	}

}

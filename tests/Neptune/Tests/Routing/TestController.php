<?php

namespace Neptune\Tests\Routing;

use Neptune\Controller\Controller;

/**
 * TestController
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TestController extends Controller {

	public function index() {
		return 'test route';
	}

	public function withEcho() {
		echo 'hello from echo';
		return 'return value';
	}

	public function echos() {
		echo 'testing';
	}

	public function nothing() {
		//do nothing
	}

}

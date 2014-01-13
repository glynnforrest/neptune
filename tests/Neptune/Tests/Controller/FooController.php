<?php

namespace Neptune\Tests\Controller;

use Neptune\Controller\Controller;

/**
 * FooController
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FooController extends Controller {

	public function fooAction() {
		return 'Foo';
	}

}

<?php

namespace {namespace}\controller;

use neptune\controller\Controller;
use neptune\view\View;
use neptune\assets\Assets;

class HomeController extends Controller {

	public function index() {
		return View::load('index');
	}

}
?>

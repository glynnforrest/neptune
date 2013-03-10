<?php

namespace Neptune\Helpers;

use Neptune\Core\Config;
use Neptune\Helpers\Html;

require_once dirname(__FILE__) . '/../test_bootstrap.php';

/**
 * HtmlTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class HtmlTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		Config::create('testing');
		Config::set('root_url', 'myapp.local');
	}

	public function tearDown() {
		Config::unload();
	}

	public function testJs() {
		$this->assertEquals('<script type="text/javascript" src="http://myapp.local/js/test.js"></script>' . PHP_EOL, Html::js('js/test.js'));
	}

	public function testJsOptions() {
		$this->assertEquals('<script type="text/javascript" src="http://myapp.local/js/test.js" id="my_script" class="script"></script>' . PHP_EOL, Html::js('js/test.js', array(
		'id' => 'my_script', 'class' => 'script')));
	}

	public function testJsAbsolute() {
		$this->assertEquals('<script type="text/javascript" src="http://site.com/js/test.js"></script>' . PHP_EOL, Html::js('http://site.com/js/test.js'));
	}

	public function testCss() {
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://myapp.local/css/style.css" />' . PHP_EOL, Html::css('css/style.css'));
	}

	public function testCssOptions() {
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://myapp.local/css/style.css" id="my_style" class="style" />' . PHP_EOL, Html::css('css/style.css', array(
		'id' => 'my_style', 'class' => 'style')));
	}

	public function testCssAbsolute() {
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://site.com/css/style.css" />' . PHP_EOL, Html::css('http://site.com/css/style.css'));
	}

	public function testEscape() {
		$this->assertEquals('&lt;p&gt;Paragraph&lt;/p&gt;', Html::escape('<p>Paragraph</p>'));
	}

	public function testInputToken() {
		$_SESSION['csrf_token'] = md5('token');
		$this->assertEquals('<input type="hidden" name="csrf_token" value="94a08da1fecbb6e8b46990538c7b50b2" />', Html::inputToken());
	}


}
?>

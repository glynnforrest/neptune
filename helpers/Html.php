<?php

namespace neptune\helpers;

use neptune\http\Session;

/**
 * Html
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Html {

	public static function inputToken() {
		return '<input type="hidden" name="csrf_token" value="' . Session::token() . '" />';
	}

	public static function escape($string) {
		return htmlentities($string, ENT_QUOTES, 'UTF-8', false);
	}

	public static function js($src, $options = array()) {
		return '<script type="text/javascript" src="' . Url::to($src) . '"' . self::options($options).'></script>'.PHP_EOL;
	}

	public static function css($src, $options = array()) {
		return '<link rel="stylesheet" type="text/css" href="' . Url::to($src) . '"' . self::options($options).' />'.PHP_EOL;
	}

	protected static function options($options = array()) {
		$text = array();
		foreach($options as $k => $v) {
			if(is_numeric($k)) {
				$k = $v;
			}
			$text[] = $k . '="' . $v . '"';
		}
		return empty($text) ? '' : ' ' . implode(' ', $text);
	}

	public static function openTag($tag, $options) {
		return '<' . $tag . self::options($options) . '>';
	}

	public static function closeTag($tag) {
		return '</' . $tag . '>';
	}

	public static function input($type, $name, $value = null, $options = array()) {
		if($type === 'password') {
			$value = null;
		}
		return '<input type="'. $type . '" name="' . $name . '" value="' .
			$value . '"' . self::options($options) . '/>';
	}

	public static function select($name, $values, $selected = null, $options = array()) {
		$options['name'] = $name;
		$text = self::openTag('select', $options);
		foreach($values as $k => $v) {
			if(is_numeric($k)) {
				$k = $v;
			}
			if($v === $selected) {
				$text .= self::openTag('option', array('value' => $v, 'selected'));
			} else {
				$text .= self::openTag('option', array('value' => $v));
			}
			$text .= $k .  self::closeTag('option');
		}
		$text .= self::closeTag('select');
		return $text;

	}


}

?>

<?php

namespace Neptune\Helpers;

use Neptune\Http\Session;
use Neptune\Helpers\Url;

/**
 * Html
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Html {

	public static function escape($string) {
		return htmlentities($string, ENT_QUOTES, 'UTF-8', false);
	}

	public static function options($options = array()) {
		$text = array();
		if(!is_array($options)) {
			$type = gettype($options);
			throw new \Exception(
				"Html::$options() must be passed an array, $type given."
			);
		}
		foreach($options as $k => $v) {
			if(is_numeric($k)) {
				$k = $v;
			}
			$text[] = $k . '="' . $v . '"';
		}
		return empty($text) ? '' : ' ' . implode(' ', $text);
	}

	public static function openTag($tag, $options = array()) {
		return '<' . $tag . self::options($options) . '>';
	}

	public static function closeTag($tag) {
		return '</' . $tag . '>';
	}

	public static function tag($tag, $content = null, $options = array()) {
		return self::openTag($tag, $options) . $content . self::closeTag($tag);
	}

	public static function selfTag($tag, $options = array()) {
		return '<' . $tag . self::options($options) . ' />';
	}

	public static function input($type, $name, $value = null, $options = array()) {
		if($type === 'textarea') {
			$options = array_merge(array('name' => $name), $options);
			return self::tag('textarea', $value, $options);
		}
		if($type === 'password') {
			$value = null;
		}
		$options = array_merge(array('type' => $type, 'name' => $name, 'value' => $value), $options);
		return self::selfTag('input', $options);
	}

	public static function inputToken() {
		return self::input('hidden', 'csrf_token', Session::token());
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
			$text .= $k . self::closeTag('option');
		}
		$text .= self::closeTag('select');
		return $text;
	}

	public static function js($src, $options = array()) {
		$options = array_merge(array(
			'type' => 'text/javascript',
			'src' => Url::to($src)), $options);
		return self::tag('script', null, $options) . PHP_EOL;
	}

	public static function css($src, $options = array()) {
		$options = array_merge(array(
			'rel' => 'stylesheet',
			'type' => 'text/css',
			'href' => Url::to($src)), $options);
		return self::selfTag('link', $options) . PHP_EOL;
	}

}

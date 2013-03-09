<?php

namespace neptune\helpers;

/**
 * String
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class String {

	const ALPHA = 0;
	const ALPHANUM = 1;
	const NUM = 2;
	const HEX = 3;

	protected static $chars = array(
		'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
		'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
		'0123456789',
		'0123456789ABCDEF'
	);

	protected static $ignored = array(
	'jeans', 'scissors', 'fish', 'sheep'
	);

	protected static $plurals = array (
		'`(l|m)ouse`' => '\1ice',
		'`([^aeiou])y$`' => '\1ies',
		'`o$`' => 'oes',
		'`(t|p)us$`' => '\1i',
		'`(a|e|i|o|u)s$`' => '\1ses',
		'`(sh|x|ch)$`' => '\1es',
		'`s$`' => 's',
		'`$`' => 's'
	);

	protected static $singles = array(
		'`ies$`' => 'y',
		'`hoes$`' => 'hoe',
		'`((a|e|i|o|u){2})ses$`' => '\1se',
		'`([^l|g])es$`' => '\1',
		'`s$`' => '',
		'`ice$`' => 'ouse',
		'`i$`' => 'us',
	);

	public static function spaces($string) {
		return str_replace(array('_', '-'), ' ', $string);
	}

	/**
	 * Convert a list of values to a string, seperating each value
	 * with a delimeter.
	 */
	public static function joinList($array, $delimeter = ', ') {
		$count = count($array) - 1;
		$string = '';
		for ($i = 0; $i < $count; $i++) {
			$string .= $array[$i] . $delimeter;
		};
		return $string . $array[$count];
	}

	public static function slugify($string) {
		$string = strtolower(preg_replace('/[^a-zA-Z0-9-]+/', ' ', $string));
		return str_replace(' ', '-', trim($string));
	}

	public static function random($length, $type = self::ALPHA) {
		$max = strlen(self::$chars[$type]) - 1;
		$str = '';
		for ($i = 0; $i < $length; $i++) {
			$index = rand(0, $max);
			$str .= self::$chars[$type][$index];
		}
		return $str;
	}

	public static function plural($string) {
		if(in_array($string, self::$ignored)) {
			return $string;
		}
		foreach(self::$plurals as $pattern => $plural) {
			if(preg_match($pattern, $string)) {
				return preg_replace($pattern, $plural, $string);
			}
		}
		return $string;
	}

	public static function single($string) {
		if(in_array($string, self::$ignored)) {
			return $string;
		}
		foreach(self::$singles as $pattern => $single) {
			if(preg_match($pattern, $string)) {
				return preg_replace($pattern, $single, $string);
			}
		}
		return $string;
	}

}

?>

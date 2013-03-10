<?php

namespace neptune\core;

use neptune\exceptions\NamespaceException;
use neptune\exceptions\ClassNotFoundException;
use neptune\exceptions\FileException;
use neptune\core\Neptune;

class Loader {

	protected static $namespace_paths = array();
	protected static $aliases = array();

	public static function load($class) {
		if (array_key_exists($class, self::$aliases)) {
			return class_alias(self::$aliases[$class], $class);
		}
		$path = self::getClassPath($class);
		try {
			if (file_exists($path)) {
				include($path);
				if (class_exists($class, false) | interface_exists($class, false)) {
					return true;
				} else {
					throw new ClassNotFoundException("Class definition $class not found in $path");
				}
			} else {
				throw new FileException("File $path not found");
			}
		} catch (\Exception $e) {
			Neptune::dealWithException($e);
		}
	}

	public static function softLoad($class) {
		if (array_key_exists($class, self::$aliases)) {
			return class_alias(self::$aliases[$class], $class);
		}
		if (class_exists($class, false) | interface_exists($class, false)) {
			return true;
		}
		try {
			$path = self::getClassPath($class);
		} catch (\Exception $e) {
			return false;
		}
		if (file_exists($path)) {
			include($path);
			if (class_exists($class, false) | interface_exists($class, false)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	protected static function getClassPath($class) {
		$name = str_replace('\\', '/', $class);
		$namespace = strtok($name, '/');
		if (isset(self::$namespace_paths[$namespace])) {
			$path = self::$namespace_paths[$namespace];
		} else {
			throw new NamespaceException("Namespace path not found: $namespace");
		}
		return $path . substr($name, strlen($namespace) + 1) . '.php';
	}

	public static function addNamespace($name, $path) {
		if (isset(self::$namespace_paths[$name])) {
			return false;
		} else {
			self::$namespace_paths[$name] = $path;
			return true;
		}
	}

	public static function addAliases(array $aliases) {
		foreach ($aliases as $k => $v) {
			self::$aliases[$k] = $v;
		}
	}

}

?>

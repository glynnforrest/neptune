<?php

namespace neptune\cache;

use neptune\exceptions\MethodNotFoundException;
use neptune\cache\CacheFactory;

/**
 * Cacheable
 * @author Glynn Forrest <me@glynnforrest.com>
 */
abstract class Cacheable {

	public function __call($method, $args) {
		if (substr($method, -6) === 'Cached') {
			$method = substr($method, 0, -6);
			if (method_exists($this, $method)) {
				//build key
				$key = get_class($this) . '/' . $method;
				foreach($args as $k => $v) {
					$key .= $k . '=' . $v . '&';
				}
				//check it exists. if it does, return the value
				$result = CacheFactory::getCacheDriver()->get($key);
				if($result) {
					return $result;
				}
				//doesn't, lets call the function and cache it
				$result = call_user_func_array(array($this, $method), $args);
				if($result) {
					CacheFactory::getCacheDriver()->set($key, $result);
					return $result;
				}
			}
		} else {
			throw new MethodNotFoundException('Function not found: ' . $method);
		}
	}

}

?>

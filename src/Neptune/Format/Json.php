<?php

namespace Neptune\Format;

/**
 * Json
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Json extends Format {

	public function read($content) {
		$this->content = json_decode($content);
	}

	public function encode() {
		if(count($this->content) === 1) {
			return json_encode(reset($this->content));
		}
		return json_encode($this->content);
	}

}
?>

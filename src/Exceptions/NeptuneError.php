<?php

namespace neptune\exceptions;

use \Exception;

/**
 * NeptuneError
 * @author Glynn Forrest me@glynnforrest.com
 * This class allows any php error to be handled as an Exception.
 **/
class NeptuneError extends Exception {

	public function __construct($errno, $errstr, $errfile, $errline, $errcontext) {
		$this->code = $errno;
		$this->message = $errstr;
		$this->file = $errfile;
		$this->line = $errline;
	}
}
?>

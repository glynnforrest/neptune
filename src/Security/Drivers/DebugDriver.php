<?php

namespace neptune\security\drivers;

/**
 * DebugDriver
 * @author Glynn Forrest me@glynnforrest.com
 **/
class DebugDriver implements SecurityDriver {


	public function loggedIn() {
		return true;
	}

	public function login($identifier, $password) {
		return true;
	}

	public function logout() {
		return true;
	}

}
?>

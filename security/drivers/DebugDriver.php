<?php

namespace neptune\security\drivers;

use neptune\security\drivers\SecurityDriver;
use neptune\security\User;

/**
 * DebugDriver
 * @author Glynn Forrest me@glynnforrest.com
 **/
class DebugDriver implements SecurityDriver {

	public function hasRole(User $u, $role) {
		return true;
	}

	public function loggedIn(User $u, $role) {
		return true;
	}

}
?>

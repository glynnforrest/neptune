<?php

namespace neptune\security\drivers;

use neptune\security\User;

/**
 * SecurityDriver
 * @author Glynn Forrest me@glynnforrest.com
 **/
interface SecurityDriver {

	public function hasRole(User $u, $role);

}
?>

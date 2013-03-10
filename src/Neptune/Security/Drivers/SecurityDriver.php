<?php

namespace Neptune\Security\Drivers;

/**
 * SecurityDriver
 * @author Glynn Forrest me@glynnforrest.com
 **/
interface SecurityDriver {

	public function loggedIn();

	public function login($identifier, $password);

	public function logout();

}
?>

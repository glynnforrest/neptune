<?php

namespace Neptune\Tests\Tasks;

use Neptune\Tasks\Task;

/**
 * DummyTask
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DummyTask extends Task {

	public function getTaskMethodsForTesting() {
		return $this->getTaskMethods();
	}

}

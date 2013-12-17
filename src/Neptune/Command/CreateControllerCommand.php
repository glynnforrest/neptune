<?php

namespace Neptune\Command;

use Neptune\Command\CreateCommand;
use Neptune\View\Skeleton;

use Stringy\StaticStringy as S;

/**
 * CreateControllerCommand
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CreateControllerCommand extends CreateCommand {

	protected $name = 'create:controller';
	protected $description = 'Create a new controller and test';
	protected $prompt = 'Controller name: ';
	protected $default = 'Home';

	protected function getTargetPath($name) {
		return 'Controller/' . S::UpperCamelize($name) . 'Controller.php';
	}

	protected function getSkeleton($name) {
		$skeleton = Skeleton::loadAbsolute($this->getSkeletonPath('controller'));
		$name = S::UpperCamelize($name) . 'Controller';
		$skeleton->controller_name = $name;
		return $skeleton;
	}

}

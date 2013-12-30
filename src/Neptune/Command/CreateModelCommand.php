<?php

namespace Neptune\Command;

use Neptune\Command\CreateCommand;
use Neptune\View\Skeleton;

use Stringy\StaticStringy as S;

/**
 * CreateModelCommand
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CreateModelCommand extends CreateCommand {

	protected $name = 'create:model';
	protected $description = 'Create a new model and test';
	protected $prompt = 'Model name: ';
	protected $default = 'User';

	protected function getTargetPath($name) {
		return 'Model/' . S::UpperCamelize($name) . 'Model.php';
	}

	protected function getSkeleton($name) {
		$skeleton = Skeleton::loadAbsolute($this->getSkeletonPath('model'));
		$name = S::UpperCamelize($name) . 'Model';
		$skeleton->model_name = $name;
		return $skeleton;
	}

}

<?php

namespace Neptune\Command;

use Neptune\Command\CreateCommand;
use Neptune\View\Skeleton;

use Crutches\Inflector;

use Stringy\StaticStringy as S;

/**
 * CreateThingCommand
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CreateThingCommand extends CreateCommand {

	protected $name = 'create:thing';
	protected $description = 'Create a new thing and test';
	protected $prompt = 'Entity name (a singular noun): ';
	protected $default = 'User';

	protected function getResourceFilename($name) {
		$name = S::UpperCamelize($name);
		$filename = $this->getAppDirectory() . 'Thing/' . $name . '.php';
		return $filename;
	}

	protected function getSkeleton($name) {
		$skeleton = Skeleton::loadAbsolute($this->getSkeletonPath('thing'));
		$name = S::UpperCamelize($name);
		$skeleton->thing_name = $name;
		$skeleton->table = Inflector::locale()->plural(S::slugify($name, '_'));
		return $skeleton;
	}

}
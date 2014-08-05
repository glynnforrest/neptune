<?php

namespace Neptune\Command;

use Neptune\Command\CreateCommand;
use Neptune\View\Skeleton;

use Crutches\Inflector;

use Stringy\StaticStringy as S;

/**
 * CreateEntityCommand
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CreateEntityCommand extends CreateCommand {

	protected $name = 'create:entity';
    protected $description = 'Create a new active doctrine entity and test';
	protected $prompt = 'Entity name (a singular noun): ';
	protected $default = 'User';

	protected function getTargetPath($name) {
		return 'Entity/' . S::UpperCamelize($name) . '.php';
	}

	protected function getSkeleton($name) {
        $skeleton = new Skeleton($this->getSkeletonPath('entity'));
		$name = S::UpperCamelize($name);
		$skeleton->entity_name = $name;
		$skeleton->table = Inflector::locale()->plural(S::slugify($name, '_'));
		return $skeleton;
	}

}
<?php

namespace Neptune\Command;

use Neptune\Command\CreateCommand;
use Neptune\View\Skeleton;

use Stringy\StaticStringy as S;

/**
 * CreateCommandCommand
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CreateCommandCommand extends CreateCommand {

	protected $name = 'create:command';
	protected $description = 'Create a new command and test';
	protected $prompt = 'Command name: ';
	protected $default = 'say:hello';

	protected function getTargetPath($name) {
		return 'Command/' . $this->createClassName($name) . '.php';
	}

	protected function createClassName($name) {
		return S::UpperCamelize(str_replace(':', '_', $name)) . 'Command';
	}

	protected function getSkeleton($name) {
        $skeleton = new Skeleton($this->getSkeletonPath('command'));
		$skeleton->class_name = $this->createClassName($name);
		$skeleton->name = $name;
		$skeleton->description = 'Description for ' . $name;
		return $skeleton;
	}

}

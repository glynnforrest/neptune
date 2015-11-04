<?php

namespace Neptune\Command;

use Neptune\View\Skeleton;
use Stringy\StaticStringy;

/**
 * Create a new fixture.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CreateFixtureCommand extends CreateCommand
{
    protected $name = 'create:fixture';
    protected $description = 'Create a new fixture';
    protected $prompt = 'Fixture name (Users): ';
    protected $default = 'Users';

    protected function getTargetPath($name)
    {
        return 'Fixtures/'.StaticStringy::UpperCamelize($name).'.php';
    }

    protected function getSkeleton($name)
    {
        $skeleton = new Skeleton($this->getSkeletonPath('fixture'));
        $skeleton->class_name = StaticStringy::UpperCamelize($name);

        return $skeleton;
    }
}

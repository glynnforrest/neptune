<?php

namespace Neptune\Command;

use Neptune\View\Skeleton;

use Stringy\StaticStringy as S;

use Doctrine\Common\Inflector\Inflector;

/**
 * CreateEntityCommand
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CreateEntityCommand extends CreateCommand
{
    protected $name = 'create:entity';
    protected $description = 'Create a new active doctrine entity and test';
    protected $prompt = 'Entity name (a singular noun): ';
    protected $default = 'User';

    protected function getTargetPath($name)
    {
        return 'Entity/' . S::upperCamelize($name) . '.php';
    }

    protected function getSkeleton($name)
    {
        $skeleton = new Skeleton($this->getSkeletonPath('entity'));
        $name = S::upperCamelize($name);
        $skeleton->entity_name = $name;
        $skeleton->table = Inflector::pluralize(S::replace(S::dasherize($name), '-', '_'));

        return $skeleton;
    }

}

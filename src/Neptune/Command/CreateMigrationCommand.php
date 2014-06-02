<?php

namespace Neptune\Command;

use Neptune\View\Skeleton;

/**
 * CreateMigrationCommand
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CreateMigrationCommand extends CreateCommand
{

    protected $name = 'create:migration';
    protected $description = 'Create a new migration';

    protected function getTargetPath($name)
    {
        return 'Migrations/' . $name . '.php';
    }

    protected function getResourceName()
    {
        return 'Migration' . date('YmdHis') ;
    }

    protected function getSkeleton($name)
    {
        $skeleton = new Skeleton($this->getSkeletonPath('migration'));
        $skeleton->class_name = $name;

        return $skeleton;
    }

}

<?php

namespace Neptune\Tasks;

use Neptune\Tasks\Task;
use Neptune\Helpers\String;
use Neptune\View\Skeleton;
use Neptune\Core\Config;
use Neptune\Exceptions\FileException;

/**
 * CreateTask
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CreateTask extends Task {

    public function run() {
        //interactively choose function to run
    }

    public function controller($name = null) {
        if(!$name) {
            $name = $this->console->read('Controller name', 'Home');
        }
        $name = String::camelCase($name, true) . 'Controller';
        $new_file = $this->getAppDirectory() . 'Controller/' . $name . '.php';
        $c = Config::load('neptune');
        $skeleton_path = $c->getRequired('dir.neptune') . '/src/Neptune/Skeletons/Controller';
        $skeleton = Skeleton::loadAbsolute($skeleton_path);
        $skeleton->controller_name = $name;
        try {
            $skeleton->saveSkeleton($new_file);
            $this->console->write("Creating $new_file");
        } catch (FileException $e){
            //ask to overwrite the file
            if($this->console->readYesNo("$new_file exists. Overwrite?")) {
                $skeleton->saveSkeleton($new_file, true);
                $this->console->write("Creating $new_file");
            }
        }
    }

}

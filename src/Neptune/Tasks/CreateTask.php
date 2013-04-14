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

    protected function saveSkeletonToFile(Skeleton $skeleton, $file) {
        try {
            $skeleton->saveSkeleton($file);
            $this->console->write("Creating $file");
        } catch (FileException $e){
            //ask to overwrite the file
            if($this->console->readYesNo("$file exists. Overwrite?")) {
                $skeleton->saveSkeleton($file, true);
                $this->console->write("Creating $file");
            }
        }
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
        $this->saveSkeletonToFile($skeleton, $new_file);
    }

    public function model($name = null) {
        if(!$name) {
            $name = $this->console->read('Model name');
        }
        $name = String::camelCase($name, true) . 'Model';
        $new_file = $this->getAppDirectory() . 'Model/' . $name . '.php';
        $c = Config::load('neptune');
        $skeleton_path = $c->getRequired('dir.neptune') . '/src/Neptune/Skeletons/Model';
        $skeleton = Skeleton::loadAbsolute($skeleton_path);
        $skeleton->model_name = $name;
        $this->saveSkeletonToFile($skeleton, $new_file);
    }

}

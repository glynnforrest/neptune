<?php

use neptune\console\Console;
use neptune\console\Generator;

include('bootstrap.php');

$c = Console::getInstance();
$c->write('Welcome to the Neptune installer.');

$project_dir = $c->read('Create a Neptune project in: ');

if(!file_exists($project_dir)) {
  if(!@mkdir($project_dir)) {
    $c->write('Unable to create new directory ' . $project_dir);
    exit(1);
  }
}

$g = Generator::getInstance();
$g->populateAppDirectory($project_dir);
$c->write('Created blank application in ' . $project_dir);
?>

<?php

use Neptune\Console\Console;
use Neptune\Console\Generator;
use Neptune\Core\Neptune;
use Neptune\Core\Events;

include('bootstrap.php');

Neptune::handleErrors();
Events::getInstance()->addHandler('\Exception', function($e) {
	Console::getInstance()->error($e->getMessage());
	});

$c = Console::getInstance();
$c->write('Welcome to the Neptune installer.');

$project_dir = $c->read('Create a Neptune project in: ');
Neptune::set('root_namespace', $c->read('Root namespace of application: '));

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

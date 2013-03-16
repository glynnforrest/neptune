<?php

//really simple console functions before we get everything installed
function write($text) {
	echo $text . PHP_EOL;
}

function read($prompt) {
	if(extension_loaded('readline')) {
		return readline($prompt);
	}
	write($prompt);
	return fgets(STDIN);
}

write('Welcome to the Neptune installer.');

$project_dir = read('Create a Neptune project in the following directory: ');

if(!file_exists($project_dir)) {
	if(!@mkdir($project_dir)) {
		write('Unable to create new directory ' . $project_dir);
		write('Make sure the path is writeable and permissions are set correctly.');
		exit(1);
	}
}

chdir($project_dir);

$json = '{
	"require": {
		"glynnforrest/neptune":"dev-master"
	}
}
';

try {
	$file = new \SplFileObject('composer.json', 'w');
	$file->fwrite($json);
} catch (\Exception $e) {
	write($e->getMessage());
	exit(1);
}



passthru('composer install');
write('Neptune successfully downloaded.');

$neptune_loc = __DIR__ . '/neptune';
copy($neptune_loc, 'neptune');
chmod('neptune', 0775);
write('Installed neptune executable.');

write('Now change directory to ' . $project_dir . ' and run `php neptune setup`.');

exit(0);

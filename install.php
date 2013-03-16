<?php

function write($text) {
	echo $text . PHP_EOL;
}

write('Installing neptune...');

$project_dir = getcwd();

if(!is_writable($project_dir)) {
	write('Unable write to ' . $project_dir);
	write('Make sure the path is writeable and permissions are set correctly.');
	exit(1);
}

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

$neptune_loc = $project_dir . '/vendor/glynnforrest/neptune/neptune';
copy($neptune_loc, 'neptune');
chmod('neptune', 0775);
write('Installed neptune executable.');

write('Now run `php neptune setup`.');

exit(0);

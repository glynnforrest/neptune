# Neptune
### A PHP application library
### By Glynn Forrest

[![Build Status](https://travis-ci.org/glynnforrest/neptune.png)](https://travis-ci.org/glynnforrest/neptune)

Intro
-----
Neptune is a PHP library designed to take the pain out of PHP. It offers a
usable yet powerful set of components built to handle the common
problems a PHP developer encounters.

Neptune offers solutions to many of these: Database abstraction, data
validation, powerful routing, restful architectures, magic formatting,
usable views with powerful helpers and easy to use caching.

Installation
------------
Neptune is installed via Composer. To add it to your project, simply add it to your
composer.json file:

	{
		"require": {
			"glynnforrest/neptune": "0.4.*"
		}
	}

And run composer to update your dependencies:

	$ curl -s http://getcomposer.org/installer | php
	$ php composer.phar update

Alternatively, run

	$ mkdir project_name && cd project_name
	$ curl https://raw.github.com/glynnforrest/neptune/master/install.php | php

to create a blank neptune project from scratch.

Note: install.php assumes you have `composer` available as an executable on your PATH.


Documentation and support
-------------------------
Look in the docs/ folder. Better documentation is on the way.

Warning
-------
Neptune is under heavy development so expect the code to change regularly.

License
-------

MIT

<?php

//Here is a sample index file for a Neptune application.

//Give our application classes a namespace.
namespace app;

//Include the Neptune bootstrap.php file here.
require('path/to/Neptune/bootstrap.php');

//To use Neptune classes without entering their namespaces everytime
//write some use statements. Here are the classes we'll be using in this file.
use neptune\core\Dispatcher;
use neptune\core\Loader;
use neptune\core\Config;
use neptune\core\Events;
use neptune\core\Logger;
use neptune\core\Neptune;

//Let's add our 'app' namespace to the loader.
Loader::addNamespace('app','path/to/app/classes');

//Often it's useful to add aliases for classes so we can use them without
//writing a use statement. This is very handy in views.
Loader::addAliases(array('Assets' => 'neptune\\helpers\\Assets'));

//Load a configuration file.
Config::load('config', 'path/to/config.php');

//Enable logging.
Logger::enable();

//We'll allow Neptune to handle all exceptions and throw errors as NeptuneErrors.
Neptune::handleErrors();

//Register our application's SecurityDriver.
SecurityFactory::registerDriver('my_driver', '\\app\\MyDriver');

//By throwing errors as NeptuneErrors we can catch of all them with the
//following event handler:
Events::getInstance()->addHandler('\Exception', function($e) {
	//Let's just log the error for now and then echo it to the browser.
	$msg = $e->getCode() . ' ' . $e->getMessage() .
		' '. $e->getFile() . ' ' . $e->getLine();
	Logger::fatal($msg);
	echo $e;
});


//Ok, time for some routing. First grab a Dispatcher instance:
$d = Dispatcher::getInstance();
//Set some globals. Here we'll change all :controller variables to
//\\app\\controller\\:controllerController
$d->globals()->transforms('controller', function($string) {
	return '\\app\\controller\\' . ucfirst($string) . 'Controller';
});
//Time to add the routes. Be aware that the order you define them will be the order
//they are matched, so if your first route passes any others will be ignored.
//Let's make a simple route that matches the string 'hello', calling the 'hello'
//method of the 'world' controller.
$d->route('hello')->controller('world')->method('hello');
//Let's create an auto route.
$d->route('/:controller(/:method(/:args))')->method('index');
//Finally, here's a catchAll rule. This would normally be some kind of 404 page.
$d->catchAll('controller', 'method');
//All ready. Let's run the application!
$d->go();
?>

#Creating and managing assets

Neptune comes with a small but useful library for managing your assets. Dependency management, filters and compressors and package support is provided out of the box.

##Adding assets

To include an Asset in your views, call the addCss or addJs function:

	Assets::addCss('styles', '/css/styles.css');


The first argument is the name of your asset, the second is the path to the asset, relative to the assets.dir config key in your application configuration.

Sometimes an asset will depend on another asset. Pass in the name of that assets as the third argument and it'll be included after its dependencies. Use css dependencies as a way to overwrite css rules from other files.

	Assets::addJs('jquery-ui, '/js/lib/jquery-ui.min.js', 'jquery');
	Assets::addJs('my-script'/js/my-script.js', array('jquery', 'jquery-ui'));

##Using assets in your views

Call either Assets::css or Assets::js in your view and all included assets will be placed into the html, dependencies observed.

	<!DOCTYPE html>
	<html>
	  <head>
		<title>title</title>
		<?=Assets::css();?>
	  </head>
	  <body>
		<!-- site content -->
		<?=Assets::js();?>
	  </body>
	</html>

##Using assets in different packages

Instead of placing all your assets in one directory it's useful to split them up into different packages. Using neptune's 'hash' notation, you can specify a different config file to read the assets settings from.

For example, if you had a package with the config file that looked like this:

	<?php return array(
	//rest of configuration here
	'assets' => array(
		'dir' => __DIR__ . '/assets'
	);

And loaded the config like this:

	Config::load('package', 'app/package/config.php');

You'd be able to include assets in the app/package/assets directory like this:

	Assets::addCss('package-theme', 'package#css/theme.css');

##Development mode

##Cache busting

It's useful in during development to make sure the browser is not
caching any js or css files. Simply set `assets.cache_bust` to true in
your configuration to automatically add cache busting to all asset
urls.

##Using assets in production

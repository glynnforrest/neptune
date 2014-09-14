#Managing frontend assets without going mad

There are a lot of things to think about when managing assets in a web
application. Personally I like to see:

- Integration with grunt, gulp and whatever hot new build tool I
  decide to use. I want to use the tools designed for frontend
  development, not a PHP version that attempts to duplicate this
  functionality.
- Support for working with vendor libraries without committing them to
  version control.
- First class support for keeping assets in different application
  modules, with the ability to reference assets in other modules.
- Separating assets into logical groups, and then compressing them all
  down into one file when it comes to deployment.

There have been many attempts to accomplish these goals by different
frameworks in the past. They developed their own solutions for
managing assets before tools such as grunt or bower became
popular. Now they're tasked with supporting their own tools and
duplicating the amazing work of grunt, bower, yeoman, etc.

Fortunately for me these tools exist now so I can just mash them all
together. For Neptune, I've tried to implement sane asset management
in the simplest possible way, allowing you to work with all these
wonderful tools.

##Adding assets

The AssetManager class is responsible for, well, managing
assets. Adding a css or javascript file to be included is as simple as
calling the relevant method.

```php
// $am is an AssetManager, trust me
$am->addCss('/css/styles.css');
$am->addJs('/js/app.js');
$am->addJs('/js/analytics.js');
//external assets are fine too
$am->addJs('http://cdn.example.org/js/library.js');
```

The asset manager can be accessed with `$neptune['assets']`, or
`$this->assets()` if you're in a controller. I can show you how to
instantiate it manually later if you need to. Make sure to have
registered the AssetsModule.

##Using groups

Adding assets one-by-one is a pain - what works better is using asset
groups. These are defined in configuration files and makes referring
to a collection of assets very easy. Even better, we can crunch them
down into one file when it comes to deployment time.

```php
//inside my-module/config.php
'assets' => [
    'css' => [
        'main' => [
            'my-module/vendor/bootstrap.min.css',
            'site-module/font-awesome/css/font-awesome.css',
            'site-module/css/styles.css',
        ]
    ],
    'js' => [
        'main' => [
            'my-module/vendor/jquery.min.js',
            'my-module/js/my-module.js',
        ],
        'admin' => [
            '@my-module/main',
            'my-module/js/my-module-admin.js'
        ]
    ]
]
```

It's fairly straightforward - keep seperate sections for css and
javascript, then include a subsection for each group. Note how the
first part of the path is the module name. I'll explain how these
paths are resolved in a minute.

In this config, css files from 'site-module' were included, and the
'admin' javascript group imports the whole of the 'main' group using
the '@' sign.

After defining them, groups can be added using the `addCssGroup()` and
`addJsGroup()` methods.

```php
$am->addCssGroup('my-module:main');
$am->addJsGroup('my-module:admin');
```

##Including assets in views

Having done all the prep work, we can now include these assets in
templates with code that never references assets by name. This
allows for alteration of the included assets (perhaps a nice holiday
css theme) without changing the template code. The AssetsExtension
class registers `css()` and `js()` functions which covers everything you
need.

```html
<!DOCTYPE html>
<html>
  <head>
    <title>My Personal Home Page Page</title>
    <!-- css here -->
    <?=$this->css();?>
  </head>
  <body>
    <!-- amazing site content here -->
    <img src="/images/self-portrait.jpg" alt="My beautiful mugshot"/>

    <!-- js here -->
    <?=$this->js();?>
  </body>
</html>
```

It's that easy. Now, onto modules, build tools, cache busting urls and some
examples.

##Modules and resolving paths

##The AssetsController

##Cache busting

It's also useful during development to make sure the browser is not
caching any assets. Set `assets.cache_bust` to true in
config/neptune.php to add cache busting to all asset urls. It's no
replacement for a properly configured development server, and make sure
you don't leave it on for production, unless you enjoy large bandwidth bills.

##Working with build tools

Ok, now for the good stuff. Since each module can be considered its
own little assets nursery, we can create seperate processes for
managing them. One module may decide to have a grunt setup with less,
another with gulp and a custom build of bootstrap and jquery.

There are two commands to facilitate this: `assets:install` and
`assets:build.` Install should be used to download assets, install
dependencies, etc. Build should be used to process assets ready for
production. Both run arbitrary bash commands in the module directory,
which you can put in the config file of the module.

For example, our first module may have something like this:

```php
//inside my-module/config.php
'assets' => [
    'css' => [
    //you get the idea
    ],
    'js' => [
    //...
    ],
    'install' => 'bower install && grunt',
    'build' => 'grunt build'
]
```

And our second:

```php
//inside site-module/config.php
'assets' => [
    //...
    'install' => 'bower install && gulp',
    'build' => 'gulp build'
]
```

Now when we run `assets:install`, the `install` command in each module
will be run. For these two modules, the vendor libs will be
downloaded, my-module will run grunt and site-module will run gulp.

##Building assets

`assets:build` runs the `install` command, then the `build` command, and
then one extra step. After the build scripts have run the command will
link each module asset/ folder into the web directory, either
with a symlink or by copying the folder. It will then concatenate
assets that live in a group together to one file and place that in the
web directory too.

For extra points and to avoid some potentially dumb information
leakage, make sure your build scripts remove all non-essential files
from the assets/ folder, leaving out stuff like the annotated source
code, the README.md and SECURITY.md. Whoops.

## A note regarding external assets

Since these assets come from another server, they won't be
concatenated together or be subject to cache busting.

## Overriding the AssetManager

If you want to instatiate your own AssetManager, it has two
dependencies - a ConfigManager to fetch group definitions from module
config files and a TagGenerator to output the HTML.

```php
$generator = new TagGenerator('http://cdn.example.com/assets/');
$neptune['assets'] = function($neptune) {
    return new AssetManager($neptune['config.manager'], $generator);
};
```

Perhaps you have an extremely particular boss that wants to swap the
order of the 'rel' and 'type' in the css tags and add id attributes to
every script tag. In this case you should extend TagGenerator to meet
the requirements. If you decide to use your own version of a
ConfigManager to load your groups from ancient heiroglyphs - well,
you're probably better off rolling your own asset management
anyway. Remember that Config instances can be modified at runtime and
module configs can be overridden with an app specific copy, so there
is a lot of potential.

Now onto some examples!

##Examples

###An admin module that includes twitter bootstrap and some custom css and javascript

###A simple application with no modules

###A module that builds everything into one file with something like require.js

# Managing assets

Neptune has asset utilities that are designed to work with frontend
build tools such as grunt and gulp.

## Linking to assets

Both twig and php views have `js` and `css` helpers to link to assets.

Twig:

```html
<!DOCTYPE html>
<html>
  <head>
    <title>My Personal Home Page Page</title>
    {{ css('my-module/css/main.css') }}
  </head>
  <body>
    {{ js('my-module/js/admin.js') }}
  </body>
</html>
```

PHP:

```html
<!DOCTYPE html>
<html>
  <head>
    <title>My Personal Home Page Page</title>
    <?=$this->css('css/main.css');?>
  </head>
  <body>
    <?=$this->js('js/admin.js');?>
  </body>
</html>
```

> As both PHP and Twig methods are extremely similar,
> the rest of this guide will just show the Twig way only.

Each method takes the name of an asset,
relative to the config setting `neptune.assets.url`.
This defaults to `/assets`, so `css/main.css` will create a stylesheet
tag linking to `/assets/css/main.css`.
This file will be at `/<project>/public/assets/css/main.css`.

If the supplied asset begins with `/` or has a scheme (`://`),
it will be taken as-is.

### Inline assets

Use `inlineCss` and `inlineJs` to render css and js inline,
e.g. dynamically generated code.

```html
<head>
  {{ inlineCss({{ cssFromTheme }})}}
</head>
<body>
  {{ inlineJs({{ jsFromDatabase }})}}
</body>
```

## Module assets

Instead of putting all assets in the public directory,
assets can come from different modules and linked into the public directory.

```bash
neptune php assets:install
```

This will take the `assets` folder from each module and link it into the public folder.

Additionally, each set of assets may require an installation step
(e.g. installation with bower, sass compilation with grunt).


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


## Group assets

> asset groups are really only useful when not using a javascript
> module system already (e.g. require.js)

Often a page requires multiple asset files, which can be a pain to
render individually in a template.  Asset groups solve this problem by
defining a list of assets in configuration, which can be used in
multiple places with a single line of code.

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

You can then use `cssGroup` and `jsGroup` template helpers to reference these groups

### Concatenating group assets

The added bonus with asset groups is the ability to concatenate assets
to a single file to reduce the overhead of making many HTTP requests.

You can concatenate the files in asset groups together by running

```bash
neptune php assets:concatenate
```

and setting the config option `neptune.assets.concat_groups` to `true`.

Your templates should remain unmodified, but now a call to
`cssGroup('my-module:main')` will link to a single css file with
everything included, instead of many.

## Behind the scenes

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


In this config, css files from 'site-module' were included, and the
'admin' javascript group imports the whole of the 'main' group using
the '@' sign.

After defining them, groups can be added using the `addCssGroup()` and
`addJsGroup()` methods.

```php
$am->addCssGroup('my-module:main');
$am->addJsGroup('my-module:admin');
```

##Cache busting

It's also useful during development to make sure the browser is not
caching any assets. Set `assets.cache_bust` to true in
config/neptune.php to add cache busting to all asset urls. It's no
replacement for a properly configured development server, and make sure
you don't leave it on for production, unless you enjoy large bandwidth bills.

## Working with build tools

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

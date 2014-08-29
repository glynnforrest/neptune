# Views

Views in Neptune are simple PHP files.

```html
<!-- my-view.php -->
<!DOCTYPE html>
<html>
  <head>
    <title>My Personal Home Page Page</title>
  </head>
  <body>
    <?= $this->content; ?>
  </body>
</html>
```

To render a template, create a `Neptune\View\View` instance, passing the
file name of the template into the constructor.

```php
$view = new Neptune\View\View('/path/to/my-view.php');
echo $view->render();
```

## Variables and child templates

`my-view.php` references `$this->content`. Use set() to set `content` to a value.

```php
$view->set('content', 'Hello, world!');
```

The magic __set() method can do the same.

```php
$view->content = 'Hello, world!';
```

Likewise, use get() and __get() to get a variable.

```html
<body>
  <?= $this->content; ?>
  <!-- Or use get() -->
  <?= $this->get('content'); ?>
</body>
```

The second argument of get() will be returned if the variable isn't
set. It defaults to null.

```html
<!-- 'message' is not set -->

<body>
  <!-- null -->
  <?= $this->get('message'); ?>

  <!-- MESSAGE! -->
  <?= $this->get('message', 'MESSAGE!'); ?>
</body>
```

Any type of variable can be set, including other views. Be sure to
call render() to display them.

```php

use Neptune\View\View;

$view = new View('base.php');

$view->title = 'Home';
$view->page = new View('greetings.php');
$view->page->greetings = ['Hello', 'Hi', 'Greetings'];

echo $view->render();
```

```html
<!-- base.php -->

<!DOCTYPE html>
<html>
  <head>
    <title><?= $this->title; ?></title>
  </head>
  <body>
    <?= $this->page->render(); ?>
  </body>
</html>
```

```html
<!-- greetings.php -->

<?php foreach($this->get('greetings', []) as $greeting): ?>
  <p><?= $greeting; ?></p>
<?php endforeach; ?>
```

## The ViewCreator

Instead of creating View instances manually, a ViewCreator instance
can create them for you. This allows you to specify much shorter paths
and helps in creating portable code.

```php
//instead of
$view = new View('/path/to/my-module/views/template.php');

//use the load() method of ViewCreator
$view = $creator->load('my-module:template.php');
```

### Resolving template paths and overriding the template in a module

The name of the template passed to load() may be of the form <view> or
<module>:<view>. A template inside a module is by overridden by a
template with the same name inside the app/views/<module>
directory. This allows for you to override views inside a module with
a view specific to your application.

```php
/* load /app/views/my-view.php */
$view = $creator->load('my-view.php');

/* load /path/to/my-module/views/index.php */
$view = $creator->load('my-module:index.php');

/* this would have loaded /path/to/my-module/views/login.php, but
/app/views/my-modules/login.php exists. The template in /app/views/
takes precedence. */
$view = $creator->load('my-module:login.php');
```

### ViewService and controllers

If you register the ViewService, a ViewCreator will be available at
`$neptune['view']`.

Controllers take it one step further - there is a `view` method that
will load a template for you.

```php
public function helloAction(Request $request)
{
    return $this->view('my-module:hello');
}
```

## ViewExtensions

ViewCreator also allows for extensions to be registered and available
inside templates. To add an Extension, implement
`Neptune\View\Extension\ExtensionInterface` and add it to the
ViewCreator with the `addExtension` method.

ViewService registers a few extensions by default:

### AssetsExtension

Used to render css and javascript files using the AssetManager,
registered with the AssetsModule.

Available methods:

* `css` - call AssetManager#css().
* `js` - call AssetManager#js().
* `assets` - get the AssetManager.

### SecurityExtension

Used to work with components from the Blockade library, registered
with the SecurityService.

Available methods:

* `hasRole` - check if the user has a given role.
* `loggedIn` - check if the user is logged in.
* `getUser` - get the current user, if they are logged in.

## Composition over inheritance

For the sake of simplicity, a deliberate design decision was made to
avoid any kind of template inheritance. Child views need to be
explicitly set and rendered, and variables set in one view aren't
available in other views.

While on the surface it may appear that this cripples templating, it
simply encourages a different style. Instead of getting a child view
to extend a parent, a child view is added on to a parent. This
approach offers a few advantages.

* Child views can be modified and overwritten, perhaps by some kind of
  event listener.
* The template path could be changed dynamically, depending on some
  kind of condition, using the `setView` method.
* View objects can be passed around before rendering. For example, a
  view may be passed to a series of decorators. Each of these
  decorators could add variables before finally rendering the view.

Of course, there are drawbacks too. You may find preparing templates
to be verbose and the lack of inheritance frustrating. Those who would
prefer inheritance should check out twig!

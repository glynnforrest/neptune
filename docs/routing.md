# Routing

The routing component is made up of two simple parts - *routes* and a
*router*.

A *route* is simply a set of requirements, such as the url and http
method, which can inspect a Symfony Request object. If the request
matches these requirements, it will return the *controller*, *action*
and any required *arguments* to be used to serve the request.

* A `controller` is a class used to respond to requests.
* An `action` is a a method on a controller class that returns a
  response.
* `arguments` are any required arguments to an `action` method.

The *router* takes groups of *routes* and checks them one by one until
it finds a suitable way to handle a request.

To enable routing, register the RoutingService:

```php
$neptune->addService(new RoutingService());
```

This will make `$neptune['router']` available.

## Defining routes

Add a new route with the router's `route` method:

```php
$router = $neptune['router'];
$router->route('/hello/:name', 'my-module:foo', 'hello')
```

This will create a route that will respond to a url of `/hello` with
`FooController` from the `my-module` module, action `helloAction`, and
the value of `:name` as the only argument.

Here's an example of what FooController could look like:

```php
namespace MyModule\Controller;

use Neptune\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FooController extends Controller
{

    public function helloAction(Request $request, $name)
    {
        return new Response("Hello $name!");
    }

}
```

No matter the amount of arguments, the Request object is always passed
into the action first.

## Routing inside modules

In most applications, routes should be defined inside of modules with
the `routes` method. This allows you to reuse routes and controllers
across different applications. Here's a simple module which defines
routes for a contact form:

```php
class ContactModule extends AbstractModule
{

    public function routes(Router $router, $prefix, Neptune $neptune)
    {
        $router->route('$prefix', 'contact-module:contact', 'show');
        $router->route('$prefix/submit', 'contact-module:contact', 'submit);
    }

    public function getName()
    {
        return 'contact-module';
    }

    public function register(Neptune $neptune)
    {
    ///
    }

    public function boot(Neptune $neptune)
    {
    ///
    }
}
```

## Route requirements

The `route` method of Router returns the Route instance, allowing for
addition of further requirements.

```php
$route = $router->route('/foo');
```

### Url

The url to match against. Variables begin with `:`. Fragments of the
route surrounded in brackets are optional.

```php
$route->url('/foo'); // match '/foo'
$route->url('/hello/:name') // :name variable is required
$route->url('/hello(/:name)') // :name variable not required
$route->url('/(default/)hello') // '/hello' and '/default/hello' both match
```

### Controller

The controller class to use, in the format `<module>:<controller>`. If
the controller begins with `::`, the service with that name will be
used.

```php
$route->controller('my-module:foo'); // FooController from my-module
$route->controller('admin:admin/setting); // Admin\SettingsController from admin
$route->controller('::controller-service') // The controller-service service
```

### Action

The method on the controller to use.

### Arguments

The arguments to pass to the action. Any additional arguments that are
found while testing the request will be merged.

```php
$route = $router->route('/hello/:name');
$route->args(['lang' => 'en']);
// '/hello/glynn' will give args of ['lang' => 'en', 'name' => 'glynn']
```

The args method can also be used to ensure arguments are given to the
action in a particular order.

```php
$route = $router->route('/hello/:name');
$route->args(['name' => 'world', 'lang' => 'en']);
// '/hello/glynn' will give args of ['name' => 'glynn', 'lang' => 'en']
```

## Routing a standard request

Changelog
=========

### 0.4.2 2014-04-22

* Adding modules, which provide services, routes and configuration.
* Routes must now be registered through a module.
* Adding security `ConfigDriver` which reads credentials from configuration.
* Adding go() shortcut method to create a request, handle it and terminate.

### 0.4.1 2014-04-13

* Replacing cache component with doctrine/cache.
* Adding `CacheService`.
* Updating router to use doctrine/cache.
* Removal of RMT for versioning.

### 0.4.0 2014-04-02

Major internal rewrite using http-foundation, http-kernel and
pimple. Improvements to database code and active record classes, and
introduction of database migrations. Lots security improvements.
Replacing form and validation with glynnforrest/reform. Adding monolog
for logging.

* Replacing the `Neptune\Http` namespace with symfony/http-foundation.
* The request / response process is now done using symfony/http-kernel.
* Routing is handled by `RouterListener`, which listens to the
  `kernel.request` event.
* Using pimple/pimple as the dependency injection container for the
  framework. Services are now registered by implementing
  `ServiceInterface` and registering objects with the container.
* Adding built in services: `DatabaseService`, `FormService`,
  `MonologService`, `SecurityService`, `SessionService`.
* Renaming `Thing` and `ThingCollection` to `Entity` and `EntityCollection`.
* Beginning to implement relations between entities.
* Creating a `MigrationRunner` which can be called using
  `database:migrate:latest` and `database:migrate:version`.
* Simplifying database drivers and factory.
* Replacing form and validation with glynnforrest/reform.
* Rewrite of security code to use the http-kernel. Security exceptions
  are thrown, caught by the kernel and handled by `Resolver`
  instances, returning a response. `Firewall` can check incoming
  requests and throw these exceptions automatically.
* Adding `CsrfManager` to check CSRF tokens.

### 0.3.0 2014-01-13

Cache and command improvements.

* Adding file cache driver.
* Console commands from other modules are autoloaded.
* Add `cache:flush` command.
* Add global --env option to console commands.

### 0.2.4 2013-12-30

Console commands and helpers.

* `env:list`, `env:switch`, `env:create`, `env:remove` commands to
  manage environments.
* `create:controller`, `create:model`, `create:module`, `create:thing`
  for class creation.
* `setup` and `config:dirs` to set up a new application.
* `shell` to run commands in a shell.
* `assets:build` to build assets.
* Various helper methods to `Console`.
* Removal of `Bitmask` - this now lives in glynnforrest/crutches.
* Removal of old console task classes.

### 0.2.3 2013-12-15

Improvements on forms and the start of using symfony/console for
console commands.

* Adding symfony/console, along with custom `Application`, `Command`,
  `Shell`, and `OutputFormatter` classes.
* Adding option methods to `FormRow`.
* Adding checkbox support to forms.
* Simplifying `Config` class by using `Crutches\DotArray`.

### 0.2.2 2013-11-27

* Fully incorporating the Stringy library.
* Removing the old String helper.

### 0.2.1 2013-11-27

Small, internal release - improving testing and versioning.

### 0.2.0 2013-11-26

Big improvement on forms - almost a complete rewrite - and form
related html.

* Cleaning up the Html helper class and test.
* Adding Html::label()
* The Form class does less - each Form contains a group of FormRow
  instances that handle rendering inputs, labels and error messages.
* Addition of the Stringy library for creating nice looking form
  labels automatically.

### 0.1.1 2013-11-22

Internal release - adding RMT for versioning.

### 0.1.0 2013-11-20

Initial release. This library is likely to change a lot so expect
breakage.

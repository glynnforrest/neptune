# Neptune

[![Build Status](https://travis-ci.org/glynnforrest/neptune.png)](https://travis-ci.org/glynnforrest/neptune)

Neptune is a PHP 5.4+ framework built on the Symfony HttpKernel. It is
designed to be quick and easy to use while allowing room for
customization and expansion.

The framework originally started out as a hobby project, but has since
found use in non-trivial applications too. I'm also using it as an
example of my PHP skills to show potential clients and employers.

That being said, Neptune is not a toy project. It is built on solid,
battle tested components and I intend to support it for the
foreseeable future. The goal is to get it to version 1.0, where it
will then be considered feature complete. This version will be
supported with subsequent bugfixes and dependency updates.

## Components overview

The framework is built on a set of robust, reusable components:

* [Symfony HttpKernel](https://github.com/symfony/HttpKernel) - A solid foundation with many benefits.
* [Pimple](https://github.com/fabpot/Pimple) - A lightweight dependency injection container.
* [Doctrine DBAL](https://github.com/doctrine/dbal) - A robust database abstraction layer.
* [ActiveDoctrine](https://github.com/glynnforrest/active-doctrine) - Active record using the Doctrine DBAL.
* [Reform](https://github.com/glynnforrest/reform) - Forms that render and validate with ease.
* [Blockade](https://github.com/glynnforrest/blockade) - Firewall and security for the HttpKernel.
* [Monolog](https://github.com/Seldaek/monolog) - PSR-3 compatible logging for a variety of platforms.

## Additional features

The framework itself boasts some cool features:

* A robust module system for structuring applications into reusable
  chunks.
* A straightforward yet powerful assets workflow, allowing for easy
  integration with build tools such as grunt and bower.
* Configuration anyone can understand, in PHP!
* An extensible PHP template system (twig is also available).
* A migration system that makes a distinction between different
  modules, built on the Doctrine DBAL. Add a new module without
  affecting the order of other migrations.
* A bunch of console commands for speeding up development and aiding
  deployment.

## Documentation

See docs/ for documentation and usage examples.

## Installation

Neptune is installed via Composer. To add it to your project, simply add it to your
composer.json file:

```json
{
    "require": {
        "glynnforrest/neptune": "0.4.*"
    }
}
```

And run composer to update your dependencies:

```bash
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar update
```

Run `vendor/bin/neptune-install .` to setup a new application.

## License

MIT, see LICENSE for details.

Copyright 2011 - 2014 Glynn Forrest

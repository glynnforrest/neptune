<?php

namespace Neptune\Tests\Service\Fixtures;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * FooType
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FooType extends Type
{

    public function getName()
    {
        return 'foo';
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'foo';
    }
}

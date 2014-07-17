<?php

namespace Neptune\Tests\Helpers;

use Neptune\Helpers\Url;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * UrlTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class UrlTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->url = new Url('myapp.local');
    }

    public function testTo()
    {
        $this->assertEquals('http://myapp.local/404', $this->url->to('404'));
    }

    public function testToFtp()
    {
        $this->assertEquals('ftp://myapp.local/file', $this->url->to('file', 'ftp'));
    }

    public function testToAbsolute()
    {
        $this->assertEquals('http://google.com', $this->url->to('http://google.com'));
        $this->assertEquals('https://google.com', $this->url->to('https://google.com'));
        $this->assertEquals('ftp://google.com', $this->url->to('ftp://google.com'));
    }

    public function testToEmptyString()
    {
        $this->assertSame('http://myapp.local/', $this->url->to());
    }

}

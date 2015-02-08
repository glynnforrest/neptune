<?php

namespace Neptune\Tests\Config;

use Neptune\Config\Config;

/**
 * ConfigTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected $config;

    public function setUp()
    {
        $this->config = new Config();
        $this->config->set('one', 'one');
        $this->config->set('two', array(
            'one' => 'two-one',
            'two' => 'two-two'
        ));
    }

    public function testSetAndGet()
    {
        $c = new Config();
        $this->assertSame($c, $c->set('foo', 'bar'));
        $this->assertSame('bar', $c->get('foo'));
    }

    public function testGetDefault()
    {
        $this->assertSame('default', $this->config->get('fake-key', 'default'));
    }

    public function testGetNoKey()
    {
        $values = array (
            'one' => 'one',
            'two' => array (
                'one' => 'two-one',
                'two' => 'two-two'
            )
        );
        $this->assertSame($values, $this->config->get());
    }

    public function testGetFirst()
    {
        $this->assertSame('two-one', $this->config->getFirst('two'));
        $this->assertSame('one', $this->config->getFirst());
    }

    public function testGetFirstDefault()
    {
        $this->assertSame('default', $this->config->getFirst('fake-key', 'default'));
    }

    public function testGetRequired()
    {
        $this->assertSame('two-one', $this->config->getRequired('two.one'));
    }

    public function testGetRequiredThrowsException()
    {
        $msg = "Required value not found: fake";
        $this->setExpectedException('Neptune\\Config\Exception\\ConfigKeyException', $msg);
        $this->config->getRequired('fake');
    }

    public function testGetRequiredEmptyString()
    {
        $this->config->set('string', '');
        $this->assertSame('', $this->config->getRequired('string'));
    }

    public function testGetFirstRequired()
    {
        $this->assertSame('two-one', $this->config->getFirstRequired('two'));
    }

    public function testGetFirstRequiredThrowsException()
    {
        $msg = "Required first value not found: fake";
        $this->setExpectedException('Neptune\\Config\Exception\\ConfigKeyException', $msg);
        $this->config->getFirstRequired('fake');
    }

    /**
     * Throw an exception if there is no array to get first value from.
     */
    public function testGetFirstRequiredThrowsExceptionNoArray()
    {
        $this->config->set('3.1', 'not-an-array');
        $msg = "Required first value not found: 3.1";
        $this->setExpectedException('Neptune\\Config\Exception\\ConfigKeyException', $msg);
        $this->config->getFirstRequired('3.1');
    }

    public function testSetNested()
    {
        $c = new Config();
        $c->set('parent.child', 'value');
        $this->assertSame(array('parent' => array('child' => 'value')), $c->get());
    }

    public function testGetNested()
    {
        $c = new Config();
        $c->set('parent', array('child' => 'value'));
        $this->assertSame('value', $c->get('parent.child'));
    }

    public function testSetDeepNested()
    {
        $c = new Config();
        $c->set('parent.child.0.1.2.3.4', 'value');
        $this->assertSame(array('parent' => array('child' => array(
            0 => array(1 => array(2 => array(3 => array(4 =>'value'))))))), $c->get());
    }

    public function testGetDeepNested()
    {
        $c = new Config();
        $c->set('parent', array('child' => array(
            0 => array(1 => array(2 => array(3 => array(4 =>'value')))))));
        $this->assertSame('value', $c->get('parent.child.0.1.2.3.4'));
    }

    /**
     * Strip out whitespace and new lines from config settings to make
     * them easier to compare against.
     */
    protected function removeWhitespace($content)
    {
        return preg_replace('`\s+`', '', $content);
    }

    public function testOverride()
    {
        $this->assertSame('one', $this->config->get('one'));
        $this->config->override(array(
            'one' => 'override',
            'two' => array(
                'three' => 'two-three'
            )
        ));
        $this->assertSame('override', $this->config->get('one'));
        $this->assertSame('two-one', $this->config->get('two.one'));
        $this->assertSame('two-three', $this->config->get('two.three'));
    }

    public function testLoop()
    {
        $config = new Config([
            'one' => 1,
            'two' => [
                'one' => 1,
                'two' => [
                    'one' => 1,
                    'two' => 2,
                ],
                'three' => 3
            ],
            'three' => 3
        ]);

        $keys = [];
        $values = [];
        foreach ($config as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }

        $expected_keys = ['one', 'two.one', 'two.two.one', 'two.two.two', 'two.three', 'three'];
        $this->assertSame($expected_keys, $keys);

        $expected_values = [1, 1, 1, 2, 3, 3];
        $this->assertSame($expected_values, $values);
    }
}

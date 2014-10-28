<?php

namespace Neptune\Tests\Config;

use Neptune\Config\Config;

use Temping\Temping;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * ConfigTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected $temp;
    protected $config;

    public function setUp()
    {
        $this->temp = new Temping();
        $this->temp->init();

        $this->config = new Config('testing');
        $this->config->set('one', 'one');
        $this->config->set('two', array(
            'one' => 'two-one',
            'two' => 'two-two'
        ));
    }

    public function tearDown()
    {
        $this->temp->reset();
    }

    public function testSetAndGet()
    {
        $c = new Config('testing');
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
        $msg = "Required value not found in Config instance 'testing': fake";
        $this->setExpectedException('Neptune\\Exceptions\\ConfigKeyException', $msg);
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
        $msg = "Required first value not found in Config instance 'testing': fake";
        $this->setExpectedException('Neptune\\Exceptions\\ConfigKeyException', $msg);
        $this->config->getFirstRequired('fake');
    }

    /**
     * Throw an exception if there is no array to get first value from.
     */
    public function testGetFirstRequiredThrowsExceptionNoArray()
    {
        $this->config->set('3.1', 'not-an-array');
        $msg = "Required first value not found in Config instance 'testing': 3.1";
        $this->setExpectedException('Neptune\\Exceptions\\ConfigKeyException', $msg);
        $this->config->getFirstRequired('3.1');
    }

    public function testSetNested()
    {
        $c = new Config('fake');
        $c->set('parent.child', 'value');
        $this->assertSame(array('parent' => array('child' => 'value')), $c->get());
    }

    public function testGetNested()
    {
        $c = new Config('fake');
        $c->set('parent', array('child' => 'value'));
        $this->assertSame('value', $c->get('parent.child'));
    }

    public function testSetDeepNested()
    {
        $c = new Config('fake');
        $c->set('parent.child.0.1.2.3.4', 'value');
        $this->assertSame(array('parent' => array('child' => array(
            0 => array(1 => array(2 => array(3 => array(4 =>'value'))))))), $c->get());
    }

    public function testGetDeepNested()
    {
        $c = new Config('fake');
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

    public function testLoadFile()
    {
        $c = new Config('testing', __DIR__ . '/fixtures/config.php');
        $this->assertSame('bar', $c->get('foo'));
    }

    public function testLoadNonExistentFileThrowsException()
    {
        $not_here = $this->temp->getPathname('not_here');
        $this->setExpectedException('Neptune\Exceptions\ConfigFileException', $not_here . ' not found');
        new Config('unlikely', $not_here);
    }

    public function testLoadInvalidFileThrowsException()
    {
        $this->temp->create('invalid.php', 'foo');
        $path = $this->temp->getPathname('invalid.php');
        $this->setExpectedException('Neptune\Exceptions\ConfigFileException', $path . ' does not return a php array');
        new Config('unlikely', $path);
    }

    public function testSave()
    {
        $this->config->set('foo', 'bar');
        $filename = $this->temp->getPathname('foo.php');
        $this->config->save($filename);
        $this->assertSame($this->removeWhitespace($this->config->toString()),
                            $this->removeWhitespace($this->temp->getContents('foo.php')));
    }

    public function testSaveNoFilename()
    {
        $this->config->set('foo', 'bar');
        $this->config->setFilename($this->temp->getPathname('bar.php'));
        $this->config->save();
        $this->assertSame($this->removeWhitespace($this->config->toString()),
                            $this->removeWhitespace($this->temp->getContents('bar.php')));
    }

    public function testSaveUsesFilename()
    {
        $file1 = $this->temp->getPathname('here.php');
        $file2 = $this->temp->getPathname('actually-here.php');
        $c = new Config('testing');
        $c->setFilename($file1);
        $c->set('foo', 'bar');
        $c->save($file2);
        //the first file shouldn't have been written
        $this->assertFalse($this->temp->exists('here.php'));
        //the second file should have been written instead
        $this->assertTrue($this->temp->exists('actually-here.php'));
    }

    public function testSaveThrowsExceptionWithNoFile()
    {
        $c = new Config('ad-hoc');
        $c->set('key', 'value');
        $msg = "Unable to save Config instance 'ad-hoc', no filename supplied";
        $this->setExpectedException('\\Neptune\\Exceptions\\ConfigFileException', $msg);
        $c->save();
    }

    public function testSaveNewFile()
    {
        $file = $this->temp->getPathname('do-not-write.php');
        $c = new Config('new');
        $c->setFilename($file);
        $c->save();
        $this->assertTrue(file_exists($file));
    }

    public function testSaveThrowsExceptionWhenFileWriteFails()
    {
        $file = $this->temp->getPathname('not-here');

        //this removes the temporary directory, preventing a write
        $this->temp->reset();

        $c = new Config('unlikely');
        $c->set('key', 'value');
        $this->setExpectedException('Neptune\Exceptions\ConfigFileException');
        $c->save($file);
    }

    public function testSetAndGetFilename()
    {
        $c = new Config('testing');
        $c->setFilename('test');
        $this->assertSame('test', $c->getFileName());
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

    public function testGetPath()
    {
        $neptune = new Config('neptune');
        $neptune->setRootDirectory('/path/to/root/');
        $neptune->set('some.dir', 'my-dir');
        $this->assertSame('/path/to/root/my-dir', $neptune->getPath('some.dir'));
    }

    public function testGetPathAbsolute()
    {
        $neptune = new Config('neptune');
        $neptune->set('some.absolute.dir', '/my-dir');
        $this->assertSame('/my-dir', $neptune->getPath('some.absolute.dir'));
    }

    public function testGetRelativePath()
    {
        $module = new Config('module');
        $module->setFilename('/some/path/to/module/config.php');
        $module->set('assets.dir', 'assets/');
        $this->assertSame('/some/path/to/module/assets/', $module->getRelativePath('assets.dir'));
    }

    public function testGetRelativePathAbsolute()
    {
        $module = new Config('module');
        $module->setFilename('/some/path/to/module/config.php');
        $module->set('some.absolute.dir', '/my-dir');
        $this->assertSame('/my-dir', $module->getRelativePath('some.absolute.dir'));
    }

    public function testToString()
    {
        $c = new Config('testing');
        $c->set('foo', 'bar');
        $expected =  '<?php return ' . var_export($c->get(), true) . '?>';
        $this->assertSame($expected, $c->toString());
    }

    public function testToStringDoesNotIncludeMerged()
    {
        $c = new Config('testing');
        $c->set('foo', 'bar');
        $c->override(array('bar' => 'baz'));
        $both = array('foo' => 'bar', 'bar' => 'baz');
        $this->assertSame($both, $c->get());
        $expected = '<?php return ' . var_export(array('foo' => 'bar'), true) . '?>';
        $this->assertSame($expected, $c->toString());
    }

    public function testToStringUsesOriginalValue()
    {
        $c = new Config('testing');
        $c->set('foo', 'bar');
        $override = array('foo' => 'override-bar', 'bar' => 'baz');
        $c->override($override);
        $this->assertSame($override, $c->get());
        $this->assertSame('override-bar', $c->get('foo'));

        //values are not changed coming from an override
        $expected =  '<?php return ' . var_export(array('foo' => 'bar'), true) . '?>';
        $this->assertSame($expected, $c->toString());

        //values are overridden when explicitly set however
        $c->set('foo', 'set-bar');
        $expected =  '<?php return ' . var_export(array('foo' => 'set-bar'), true) . '?>';
        $this->assertSame($expected, $c->toString());
    }

}

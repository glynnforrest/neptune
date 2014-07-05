<?php

namespace Neptune\Tests\View;

use Neptune\Config\ConfigManager;
use Neptune\Core\Neptune;
use Neptune\View\ViewCreator;

use Temping\Temping;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * ViewCreatorTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ViewCreatorTest extends \PHPUnit_Framework_TestCase
{

    protected $temping;
    protected $neptune;
    protected $creator;

    public function setUp()
    {
        $this->temping = new Temping();
        $this->neptune = new Neptune($this->temping->getDirectory());
        $this->creator = new ViewCreator($this->neptune);
    }

    public function tearDown()
    {
        $this->temping->reset();
    }

    protected function setupModule($name)
    {
        $module = $this->getMock('Neptune\Service\AbstractModule');
        $module->expects($this->once())
               ->method('getName')
               ->will($this->returnValue($name));
        $this->neptune->addModule($module);
        $this->temping->create('views/test-view.php');
        return $module;
    }

    protected function moduleExpectsDirectory($module)
    {
        $module->expects($this->once())
               ->method('getDirectory')
               ->will($this->returnValue($this->temping->getDirectory()));
    }

    public function testLoad()
    {
        $module = $this->setupModule('test');
        $this->moduleExpectsDirectory($module);
        $view = $this->creator->load('test:test-view');
        $this->assertInstanceOf('Neptune\View\View', $view);
        $this->assertSame($this->temping->getPathname('views/test-view.php'), $view->getView());
    }

    public function testAddAndGetHelper()
    {
        $function = function($string) {
            return strtoupper($string);
        };
        $this->assertSame($this->creator, $this->creator->addHelper('foo', $function));
        $this->assertSame(array('foo' => $function), $this->creator->getHelpers());
    }

    public function testAddAndCallHelper()
    {
        $function = function($string) {
            return strtoupper($string);
        };
        $this->assertSame($this->creator, $this->creator->addHelper('foo', $function));
        $this->assertSame('STRING', $this->creator->callHelper('foo', array('string')));
    }

    public function testAddExtensionAndGetHelpers()
    {
        $ext = $this->getMock('Neptune\View\Extension\ExtensionInterface');
        $ext->expects($this->once())
            ->method('getHelpers')
            ->will($this->returnValue(array('foo' => 'fooMethod')));
        $this->assertSame($this->creator, $this->creator->addExtension($ext));
        $functions = array(
            'foo' => array($ext, 'fooMethod')
        );
        $this->assertSame($functions, $this->creator->getHelpers());
    }

    public function testAddAndCallHelperFromExtension()
    {
        $ext = new FooExtension();
        $this->assertSame($this->creator, $this->creator->addExtension($ext));
        $this->assertSame('Foo: hello', $this->creator->callHelper('foo', array('hello')));
    }

    public function testHas()
    {
        $this->assertFalse($this->creator->has('test'));
        $this->assertFalse($this->creator->has('foo:test'));

        $this->temping->create('views/test.php');
        $module = $this->setupModule('foo');
        $this->moduleExpectsDirectory($module);
        $this->assertTrue($this->creator->has('foo:test'));
    }

    public function testLoadWithOverride()
    {
        $module = $this->setupModule('test-module');
        //create a view in the stubbed app/ directory that overrides
        //the module template
        $this->temping->create('app/views/test-module/test.php', 'FOO');
        $view = $this->creator->load('test-module:test');
        $this->assertInstanceOf('Neptune\View\View', $view);
        $filename = $this->temping->getPathname('app/views/test-module/test.php');
        $this->assertSame($filename, $view->getView());
        $this->assertSame('FOO', $view->render());
    }

    public function testLoadWithNoModule()
    {
        $this->temping->create('app/views/test.php', 'FOO');
        $view = $this->creator->load('test');
        $this->assertInstanceOf('Neptune\View\View', $view);
        $this->assertSame($this->temping->getPathname('app/views/test.php'), $view->getView());
        $this->assertSame('FOO', $view->render());
    }

}
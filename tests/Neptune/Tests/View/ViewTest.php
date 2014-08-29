<?php

namespace Neptune\Tests\View;

use Neptune\View\View;

use Temping\Temping;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * ViewTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class ViewTest extends \PHPUnit_Framework_TestCase
{
    protected $temp;
    protected $view;

    public function setUp()
    {
        $this->temp = new Temping();
        $this->temp->create('test-view.php', 'TESTING');
        $this->view = new View($this->temp->getPathname('test-view.php'));
    }

    public function tearDown()
    {
        $this->temp->reset();
    }

    public function dataProvider()
    {
        return array(
            array('string'),
            array(array()),
            array(new \stdClass),
            array(0),
            array(true),
            array(false)
        );
    }

    /**
     * @dataProvider dataProvider()
     */
    public function testSetAndGet($value)
    {
        $this->assertSame($this->view, $this->view->set('key', $value));
        $this->assertSame($value, $this->view->get('key'));
    }

    /**
     * @dataProvider dataProvider()
     */
    public function testMagicSetAndGet($value)
    {
        $this->view->key = $value;
        $this->assertSame($value, $this->view->key);
    }

    /**
     * @dataProvider dataProvider()
     */
    public function testGetWithDefault($value)
    {
        $this->assertSame('default', $this->view->get('key', 'default'));
        $this->view->key = $value;
        $this->assertSame($value, $this->view->get('key', 'default'));
    }

    public function testIsset()
    {
        $this->view->key = 'value';
        $this->assertTrue(isset($this->view->key));
        $this->assertFalse(isset($this->view->not_set));
    }

    public function testSetAndGetValues()
    {
        $values = [
            'one' => 'foo',
            'two' => ['foo', 'bar'],
            'three' => false
        ];
        $this->assertSame($this->view, $this->view->setValues($values));
        $this->assertSame('foo', $this->view->get('one'));
        $this->assertSame(['foo', 'bar'], $this->view->get('two'));
        $this->assertSame(false, $this->view->get('three'));
        $this->assertSame($values, $this->view->getValues());
    }

    public function testAddValues()
    {
        $values = ['one' => 'foo'];

        $this->assertSame($this->view, $this->view->setValues($values));
        $this->assertSame('foo', $this->view->get('one'));

        $this->assertSame($this->view, $this->view->addValues(['two' => 'bar']));
        $this->assertSame('bar', $this->view->get('two'));
        $this->assertSame(['one' => 'foo', 'two' => 'bar'], $this->view->getValues());

        $this->assertSame($this->view, $this->view->addValues(['one' => 'bar', 'three' => 'baz']));
        $this->assertSame('bar', $this->view->get('two'));
        $this->assertSame(['one' => 'bar', 'two' => 'bar', 'three' => 'baz'], $this->view->getValues());
    }

    public function testSetAndGetView()
    {
        $this->assertSame($this->temp->getPathname('test-view.php'), $this->view->getView());
        $this->assertSame($this->view, $this->view->setView('some/other/file'));
        $this->assertSame('some/other/file', $this->view->getView());
    }

    public function testRender()
    {
        $this->assertSame('TESTING', $this->view->render());
    }

    public function testInvalidViewThrowsException()
    {
        $view = new View('foo');
        $this->setExpectedException('Neptune\View\Exception\ViewNotFoundException');
        $view->render();
    }

    public function testCallHelper()
    {
        $creator = $this->getMockBuilder('Neptune\View\ViewCreator')
                        ->disableOriginalConstructor()
                        ->getMock();
        $creator->expects($this->once())
                ->method('callHelper')
                ->with('foo', array('bar', 'baz'));
        $this->view->setCreator($creator);
        $this->view->foo('bar', 'baz');
    }

    public function testRenderThrowsExceptionIfTemplateIfDeleted()
    {
        $this->temp->delete('test-view.php');
        $this->setExpectedException('Neptune\View\Exception\ViewNotFoundException');
        $this->view->render();
    }

    public function testGetCreatorThrowsException()
    {
        $msg = 'ViewCreator not set on view with template "' . $this->temp->getPathname('test-view.php') . '"';
        $this->setExpectedException('Neptune\View\Exception\ViewCreatorException', $msg);
        $this->view->getCreator();
    }

}

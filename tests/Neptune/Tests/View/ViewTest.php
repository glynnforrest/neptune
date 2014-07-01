<?php

namespace Neptune\Tests\View;

use Neptune\View\View;

use Temping\Temping;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * ViewTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class ViewTest extends \PHPUnit_Framework_TestCase {

	protected $temp;

	public function setUp() {
		$this->temp = new Temping();
	}

	public function tearDown() {
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
    public function testSetAndGet($value) {
        $v = new View('some/file');
        $this->assertSame($v, $v->set('key', $value));
        $this->assertSame($value, $v->get('key'));
    }

    /**
     * @dataProvider dataProvider()
     */
    public function testMagicSetAndGet($value)
    {
        $v = new View('some/file');
        $v->key = $value;
        $this->assertSame($value, $v->key);
    }

    /**
     * @dataProvider dataProvider()
     */
    public function testGetWithDefault($value)
    {
        $v = new View('some/template.php');
        $this->assertSame('default', $v->get('key', 'default'));
        $v->key = $value;
        $this->assertSame($value, $v->get('key', 'default'));
    }

	public function testIsset() {
		$v = new View('some/file');
		$v->key = 'value';
		$this->assertTrue(isset($v->key));
		$this->assertFalse(isset($v->not_set));
	}

    public function testGetView()
    {
        $v = new View('some/file');
        $this->assertSame('some/file', $v->getView());
    }

    public function testSetView()
    {
        $v = new View('some/file');
        $this->assertSame('some/file', $v->getView());
        $this->assertSame($v, $v->setView('some/other/file'));
        $this->assertSame('some/other/file', $v->getView());
    }

    public function testRender()
    {
        $this->temp->create('foo.php', 'testing');
        $view = new View($this->temp->getPathname('foo.php'));
        $this->assertSame('testing', $view->render());
    }

    public function testRenderInvalidView()
    {
        $v = new View('some/file');
        $this->setExpectedException('Neptune\Exceptions\ViewNotFoundException');
        $v->render();
    }

    public function testCallHelper()
    {
        $creator = $this->getMockBuilder('Neptune\View\ViewCreator')
                        ->disableOriginalConstructor()
                        ->getMock();
        $creator->expects($this->once())
                ->method('callHelper')
                ->with('foo', array('bar', 'baz'));
        $v = new View('template.php');
        $v->setCreator($creator);
        $v->foo('bar', 'baz');
    }

    public function testToStringDoesNotLeakInformation()
    {
        //The exception message thrown by render() contains the view
        //template name. Ensure __toString() does not return this
        //message, as it may leak information about the filesystem.
        $view = new View('some/non-existent-view.php');
        $this->assertNull($view->__toString());
    }

}

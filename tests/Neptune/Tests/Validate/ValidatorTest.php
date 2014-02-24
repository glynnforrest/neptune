<?php

namespace Neptune\Tests\Validate;

require_once __DIR__ . '/../../../bootstrap.php';

use Neptune\Validate\Validator;

/**
 * ValidatorTest
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase {

    public function testCheckReturnsValidator()
    {
        $v = new Validator();
        $rule = $this->getMock('Neptune\Validate\Rule\AbstractRule');
        $this->assertSame($v, $v->check('one', $rule));
    }

    public function testValidateReturnsResult() {
        $v = new Validator();
        $this->assertInstanceOf('\Neptune\Validate\Result', $v->validate(array()));
    }

    public function testValidateNoRules() {
        $v = new Validator();
        $this->assertTrue($v->validate(array())->isValid());
    }

    public function testValidateSingleRulePassing()
    {
        $v = new Validator();
        $rule = $this->getMock('Neptune\Validate\Rule\AbstractRule');
        $v->check('name', $rule);
        $rule->expects($this->once())
             ->method('validate')
             ->will($this->returnValue(true));
        $this->assertTrue($v->validate(array('name' => 'foo'))->isValid());
    }

}
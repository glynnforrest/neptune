<?php

namespace Neptune\Tests\Database\Statement;

require_once __DIR__ . '/../../../../bootstrap.php';

use Neptune\Database\Statement\QueryStatement;

/**
 * QueryStatementTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class QueryStatementTest extends \PHPUnit_Framework_TestCase
{

    protected $mock_statement;
    protected $statement;

    public function setUp()
    {
        $this->mock_statement = $this->getMock('\PDOStatement');
        $this->statement = new QueryStatement($this->mock_statement);
    }

    public function testExecuteNoParameters()
    {
        $this->mock_statement->expects($this->once())
                             ->method('execute')
                             ->with(array());
        $this->statement->execute();
    }

    public function testExecuteWithParameters()
    {
        $this->mock_statement->expects($this->once())
                             ->method('execute')
                             ->with(array('foo', 'bar'));
        $this->statement->execute(array('foo', 'bar'));
    }

    public function testExecuteWithSetParameters()
    {
        $this->mock_statement->expects($this->once())
                             ->method('execute')
                             ->with(array('foo', 'bar'));
        $this->statement->setParameters(array('foo', 'bar'));
        $this->statement->execute();
    }

    public function testExecuteWithSetAndExecuteParameters()
    {
        $this->mock_statement->expects($this->once())
                             ->method('execute')
                             ->with(array('foo', 'bar', 'baz'));
        $this->statement->setParameters(array('foo', 'bar'));
        $this->statement->execute(array('baz'));
    }

    public function testExecuteWithExpectedParameters()
    {
        $this->mock_statement->expects($this->once())
                             ->method('execute')
                             ->with(array('foo', 'bar', 'baz'));
        $this->statement->setParameters(array('foo', 'anything', 'baz'));
        $this->statement->setExpectedParameters(array(1));
        $this->statement->execute(array('bar'));
    }

    public function testExecuteWithManyExpectedParameters()
    {
        $this->mock_statement->expects($this->once())
                             ->method('execute')
                             ->with(array('one', 'two', 'three', 'four', 'five'));
        $this->statement->setParameters(array('one', null, 'three'));
        $this->statement->setExpectedParameters(array(1, 3, 4));
        $this->statement->execute(array('two', 'four', 'five'));
    }

    public function testRemainingParametersAreAdded()
    {
        $this->mock_statement->expects($this->once())
                             ->method('execute')
                             ->with(array('one', 'two', 'three', 'four', 'five'));
        $this->statement->setParameters(array('one', null, 'three'));
        $this->statement->setExpectedParameters(array(1));
        $this->statement->execute(array('two', 'four', 'five'));
    }

}

<?php

namespace Neptune\Tests\Database\Relation;

require_once __DIR__ . '/../../../../bootstrap.php';

use Neptune\Database\Relation\OneToOne;
use Neptune\Tests\Database\Fixtures\AuthorDetails;
use Neptune\Tests\Database\Fixtures\Author;

/**
 * OneToOneBelongsToTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class OneToOneBelongsToTest extends \PHPUnit_Framework_TestCase
{
    protected $author;
    protected $database;

    public function setUp()
    {
        $this->database = $this->getMock('Neptune\Database\Driver\DatabaseDriverInterface');
        $this->details = new AuthorDetails($this->database);
        $r = new OneToOne(
            $this->database,
            'id',
            'Neptune\Tests\Database\Fixtures\Author',
            'authors_id',
            'Neptune\Tests\Database\Fixtures\AuthorDetails'
        );
        $this->details->addRelation('author', $r);
    }

    public function testSetRelatedObject()
    {
        $author = new Author($this->database);
        $author->name = 'Foo';
        $this->details->author = $author;
        $this->assertSame('Foo', $this->details->author->name);
    }

    public function testForeignKeyUpdatedWhenRelated()
    {
        //details are associated with author, so foreign key is updated
        $author = new Author($this->database);
        $author->id = 1;
        $this->details->author = $author;
        $this->assertSame(1, $this->details->authors_id);
    }

   public function testForeignKeyUpdatedOnKeyChange()
   {
       $author = new Author($this->database);
       $this->details->author = $author;

       //author id is changed, so authors_id is updated
       $author->id = 3;
       $this->assertSame(3, $this->details->authors_id);
       $this->assertSame(3, $this->details->author->id);
   }

   public function testKeyNotUpdatedOnForeignKeyChange()
   {
       $author = new Author($this->database);
       $author->id = 3;
       $this->details->author = $author;
       $this->assertSame(3, $this->details->authors_id);
       $this->assertSame(3, $this->details->author->id);

       //authors_id is changed, but it shouldn't change author id
       $this->details->authors_id = 4;
       $this->assertSame(4, $this->details->authors_id);
       $this->assertSame(3, $this->details->author->id);
   }

}

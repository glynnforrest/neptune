<?php

namespace Neptune\Tests\Database\Relation;

use Neptune\Database\Relation\OneToMany;
use Neptune\Database\Entity\EntityCollection;

use Neptune\Tests\Database\Fixtures\Author;
use Neptune\Tests\Database\Fixtures\Book;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * OneToManyTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class OneToManyTest extends \PHPUnit_Framework_TestCase
{

    protected $author;

    public function setUp()
    {
        $this->database = $this->getMock('Neptune\Database\Driver\DatabaseDriverInterface');
        $this->author = new Author($this->database);
        $r = new OneToMany($this->database, 'id', 'Neptune\Tests\Database\Fixtures\Author', 'authors_id',
            'Neptune\Tests\Database\Relation\Book');
        $this->author->addRelation('books', $r);
    }

    protected function createBookCollection()
    {
        $books = new EntityCollection($this->database);
        $books->setFields(Book::getFields());
        $books->setPrimaryKey('id');
        //change this to something better - what does it even do?
        $books->setEntityClass('Neptune\\Database\\Relation\\Book');
        return $books;
    }

    /**
     * Has many functionality
     */

    public function testSetRelatedObject()
    {
        $books = $this->createBookCollection();
        $books[] = new Book($this->database, array('title' => 'Book 1'));
        $this->author->books = $books;
        $this->assertEquals('Book 1', $this->author->books->getFirst()->title);
    }

	public function testForeignKeyUpdatedWhenRelated() {
		$this->author->id = 1;

        $books = $this->createBookCollection();
        //books are not associated with an author, so foreign key can be anything
		$books->authors_id = 2;
		$this->assertSame(2, $books->authors_id);
        //hmmmm - surely a database fetch would be called here
		/* $this->assertNull($this->author->books); */

        //books are associated with author, so foreign key is updated
        $this->author->books = $books;
		$this->assertSame(1, $books->authors_id);
		$this->assertSame(1, $this->author->books->authors_id);
	}
//
// 	public function testForeignKeyUpdatedOnKeyChange() {
// 		$a = $this->author;
// 		$a->id = 1;
// 		$b = $this->books;
// 		$a->books = $b;
// 		$this->assertEquals(1, $b->authors_id);
// 		$a->id = 4;
// 		$this->assertEquals(4, $b->authors_id);
// 	}
//
// 	public function testKeyNotUpdatedOnForeignKeyChange() {
// 		$a = $this->author;
// 		$a->id = 1;
// 		$a->books = $this->books;
// 		$this->assertEquals(1, $a->books->authors_id);
// 		$a->books->authors_id = 2;
// 		$this->assertEquals(1, $a->id);
// 	}
//
// 	/**
// 	 * Belongs to functionality
// 	 */
//
// 	public function testSetOwnerObject() {
// 		$b = $this->books;
// 		$a = $this->author;
// 		$a->id = 3;
// 		$b->author = $a;
// 		$this->assertEquals(3, $b->author->id);
// 	}
//
// 	public function testKeyUpdatedOnCreateRelation() {
// 		$b = $this->books;
// 		$a = $this->author;
// 		$a->id = 3;
// 		$b->author = $a;
// 		$this->assertEquals(3, $b->authors_id);
// 	}
//
// 	public function testKeyUpdatedOnForeignKeyChange() {
// 		$b = $this->books;
// 		$a = $this->author;
// 		$a->id = 3;
// 		$b->author = $a;
// 		$a->id = 2;
// 		$this->assertEquals(2, $b->authors_id);
// 	}
//
// 	public function testRelationExistsWhenNotChildren() {
// 		$b = $this->books;
// 		$b->authors_id = 3;
// 		$a = $this->author;
// 		$a->id = 2;
// 		$this->assertEquals(2, $b->authors_id);
// 	}
//
}

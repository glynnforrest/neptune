<?php

namespace Neptune\Database\Relations;

use Neptune\Database\Thing;
use Neptune\Database\Relations\OneToMany;
use Neptune\Database\ThingCollection;

require_once __DIR__ . '/../../../bootstrap.php';

class Author extends Thing {

	protected static $table = 'authors';
	protected static $fields = array('id', 'name');
	protected static $relations = array(
		'books' => array(
			'type' => 'has_many',
			'key' => 'id',
			'other_key' => 'authors_id',
			'other_class' => 'Neptune\\Database\\Relations\\Book'
		)
	);

}

class Book extends Thing {

	protected static $table = 'books';
	protected static $fields = array('id', 'authors_id', 'title');

}

/**
 * OneToManyTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class OneToManyTest extends \PHPUnit_Framework_TestCase {

	protected $author;
	protected $books;


	public function setUp() {
		$this->author = new Author('db');
		$books = new ThingCollection('db', 'books');
		$books->setFields(array('id', 'authors_id', 'title'));
		$books->setPrimaryKey('id');
		$books->setChildClass('Neptune\\Database\\Relations\\Book');
		$books[] = new Book('db', array('title' => 'Book 1'));
		$books[] = new Book('db', array('title' => 'Book 2'));
		$this->books = $books;
		$r = new OneToMany('id', get_class($this->author), 'authors_id',
			$this->books->getChildClass());
		$this->author->addRelation('books', 'id', $r);
		// $this->books->addRelation('author', 'authors_id', $r);
	}

	public function tearDown() {
		unset($this->author);
		unset($this->books);
	}

	/**
	 * Has many functionality
	 */

	public function testSetRelatedObject() {
		$a = $this->author;
		$b = $this->books;
		$a->books = $b;
		$this->assertEquals('Book 1', $a->books[0]->title);
	}
//
// 	public function testForeignKeyUpdatedOnSetRelation() {
// 		$a = $this->author;
// 		$a->authorname = 'author1';
// 		$a->id = 1;
// 		$b = $this->books;
// 		$b->authors_id = 2;
// 		$a->books = $b;
// 		$this->assertEquals(1, $a->books->authors_id);
// 		$this->assertEquals(1, $b->authors_id);
// 	}
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
?>

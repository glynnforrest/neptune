<?php

namespace neptune\database\relations;

use neptune\model\DatabaseModel;
use neptune\database\relations\OneToMany;
use neptune\model\ModelGroup;

require_once dirname(__FILE__) . '/../../test_bootstrap.php';

class Author extends DatabaseModel {

	protected static $table = 'authors';
	protected static $fields = array('id', 'name');
	protected static $relations = array(
		 'books' => array(
			  'type' => 'has_many',
			  'key' => 'id',
			  'other_key' => 'authors_id',
			  'other_class' => 'neptune\\database\\relations\\Book'
		 )
	);

}

class Book extends DatabaseModel {

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
		$books = new ModelGroup('db', 'books');
		$books->setFields(array('id', 'authors_id', 'title'));
		$books->setPrimaryKey('id');
		$books->setChildClass('neptune\\database\\relations\\Book');
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

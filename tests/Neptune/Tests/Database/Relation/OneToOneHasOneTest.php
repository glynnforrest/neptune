<?php

namespace Neptune\Tests\Database\Relation;

require_once __DIR__ . '/../../../../bootstrap.php';

use Neptune\Database\Relation\OneToOne;

use Neptune\Tests\Database\Fixtures\AuthorDetails;
use Neptune\Tests\Database\Fixtures\Author;

/**
 * OneToOneHasOneTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class OneToOneHasOneTest extends \PHPUnit_Framework_TestCase
{
    protected $author;
    protected $database;

    public function setUp()
    {
        $this->database = $this->getMock('Neptune\Database\Driver\DatabaseDriverInterface');
        $this->author = new Author($this->database);
        $r = new OneToOne(
            $this->database,
            'id',
            'Neptune\Tests\Database\Fixtures\Author',
            'authors_id',
            'Neptune\Tests\Database\Fixtures\AuthorDetails'
        );
        $this->author->addRelation('details', $r);
    }

    /**
     * Has one functionality
     */

    public function testSetRelatedObject()
    {
        $details = new AuthorDetails($this->database);
        $details->info = 'Author biography';
        $this->author->details = $details;
        $this->assertSame('Author biography', $this->author->details->info);
    }

    public function testForeignKeyUpdatedWhenRelated()
    {
        $this->author->id = 1;
        $details = new AuthorDetails($this->database);

        //details are not associated with an author, so foreign key can be anything
        $details->authors_id = 2;
        $this->assertSame(2, $details->authors_id);

        //details are associated with author, so foreign key is updated
        $this->author->details = $details;
        $this->assertSame(1, $details->authors_id);
        $this->assertSame(1, $this->author->details->authors_id);
    }

    public function testForeignKeyUpdatedOnKeyChange()
    {
        $this->author->id = 1;
        $details = new AuthorDetails($this->database);
        $this->author->details = $details;

        //details are associated with author, so foreign key is updated
        $this->assertSame(1, $details->authors_id);
        $this->assertSame(1, $this->author->details->authors_id);

        //key is changed, so foreign key is updated
        $this->author->id = 3;
        $this->assertSame(3, $this->author->id);
        $this->assertSame(3, $details->authors_id);
        $this->assertSame(3, $this->author->details->authors_id);
    }

    public function testKeyNotUpdatedOnForeignKeyChange()
    {
        $this->author->id = 1;
        $details = new AuthorDetails($this->database);
        $this->author->details = $details;

        //details are associated with author, so foreign key is updated
        $this->assertSame(1, $details->authors_id);
        $this->assertSame(1, $this->author->details->authors_id);

        //foreign key is changed - removing the association. Key is not updated
        $details->authors_id = 3;
        $this->assertSame(1, $this->author->id);
        $this->assertSame(3, $details->authors_id);
        $this->assertSame(3, $this->author->details->authors_id);
    }

}

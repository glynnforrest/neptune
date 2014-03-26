<?php

namespace Neptune\Tests\Database\Relation;

require_once __DIR__ . '/../../../../bootstrap.php';

use Neptune\Database\Relation\RelationManager;

/**
 * RelationManagerTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class RelationManagerTest extends \PHPUnit_Framework_TestCase
{

    protected $manager;
    protected $database;

    public function setUp()
    {
        $this->database = $this->getMock('Neptune\Database\Driver\DatabaseDriverInterface');
        $this->manager = new RelationManager($this->database);
    }

    public function testCreateHasOne()
    {
        $relation = array(
            'type' => 'has_one',
            'key' => 'id',
            'other_key' => 'authors_id',
            'other_class' => 'Neptune\Tests\Database\Fixtures\AuthorDetails'
        );
        $calling_class = 'Neptune\Tests\Database\Fixtures\Author';
        $this->assertInstanceOf('\Neptune\Database\Relation\OneToOne', $this->manager->createRelation($calling_class, $relation));
    }

    public function testCreateBelongsTo()
    {
        $relation = array(
            'type' => 'belongs_to',
            'key' => 'authors_id',
            'other_key' => 'id',
            'other_class' => 'Neptune\Tests\Database\Fixtures\Author'
        );
        $calling_class = 'Neptune\Tests\Database\Fixtures\AuthorDetails';
        $this->assertInstanceOf('\Neptune\Database\Relation\OneToOne', $this->manager->createRelation($calling_class, $relation));
    }

    public function testCreateHasMany()
    {
        $relation = array(
            'type' => 'has_many',
            'key' => 'id',
            'other_key' => 'authors_id',
            'other_class' => 'Neptune\Tests\Database\Relation\Book'
        );
        $calling_class = 'Neptune\Tests\Database\Fixtures\Author';
        $this->assertInstanceOf('\Neptune\Database\Relation\OneToMany', $this->manager->createRelation($calling_class, $relation));
    }


}
<?php

namespace neptune\database;

use neptune\database\DBObject;

require_once dirname(__FILE__) . '/../../test_bootstrap.php';

class User extends DBObject {

	protected $fields = array('id', 'username');

}

class UserDetails extends DBObject {

	protected $fields = array('id', 'users_id', 'details');

}

/**
 * OneToOneTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class OneToOneTest extends \PHPUnit_Framework_TestCase {


	public function setUp() {
		$this->user = new User('db', 'users');
		$this->user->setRelation('details', array('type' => 'has_one', 'key' => 'id',
			'foreign_key' => 'users_id'));
		$this->user_details = new UserDetails('db', 'user_details');
		
	}

	public function tearDown() {
		
	}

	public function testSetRelationObject() {
		$u = $this->user;
		$d = $this->user_details;
		$d->details = 'User1 details';
		$u->details = $d;
		$this->assertEquals('User1 details', $u->details->details);
	}

	public function testForeignKeyIsChangedOnSetRelationObject() {
		$u = $this->user;
		$u->username = 'user1';
		$u->id = 1;
		$d = $this->user_details;
		$d->users_id = 2;
		$u->details = $d;
		$this->assertEquals(1, $u->details->users_id);
		$this->assertEquals(1, $d->users_id);
	}

	public function testForeignKeyIsUpdatedOnSetRelationObject() {
		$u = $this->user;
		$u->id = 1;
		$d = $this->user_details;
		$u->details = $d;
		$this->assertEquals(1, $d->users_id);
		$u->id = 4;
		$this->assertEquals(4, $d->users_id);
	}
	
}
?>

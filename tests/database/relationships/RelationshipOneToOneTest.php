<?php

namespace neptune\database;

use neptune\database\DBObject;
use neptune\database\Relationship;

require_once dirname(__FILE__) . '/../../test_bootstrap.php';

class User extends DBObject {

	protected $fields = array('id', 'username');

}

class UserDetails extends DBObject {

	protected $fields = array('id', 'users_id', 'details');

}

/**
 * RelationshipOneToOneTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class RelationshipOneToOneTest extends \PHPUnit_Framework_TestCase {

	protected $user;
	protected $user_details;


	public function setUp() {
		$this->user = new User('db', 'users');
		$r = new Relationship(Relationship::TYPE_ONE_TO_ONE, 'id', 'users_id');
		$this->user->addRelationship('details', 'id', $r);
		$this->user_details = new UserDetails('db', 'user_details');
		$this->user_details->addRelationship('user', 'users_id', $r);
	}

	public function tearDown() {
		unset($this->user);
		unset($this->user_details);
	}

	/**
	 * Has one functionality
	 */

	public function testSetRelatedObject() {
		$u = $this->user;
		$d = $this->user_details;
		$d->details = 'User1 details';
		$u->details = $d;
		$this->assertEquals('User1 details', $u->details->details);
	}

	public function testForeignKeyUpdatedOnCreateRelationship() {
		$u = $this->user;
		$u->username = 'user1';
		$u->id = 1;
		$d = $this->user_details;
		$d->users_id = 2;
		$u->details = $d;
		$this->assertEquals(1, $u->details->users_id);
		$this->assertEquals(1, $d->users_id);
	}

	public function testForeignKeyUpdatedOnKeyChange() {
		$u = $this->user;
		$u->id = 1;
		$d = $this->user_details;
		$u->details = $d;
		$this->assertEquals(1, $d->users_id);
		$u->id = 4;
		$this->assertEquals(4, $d->users_id);
	}

	public function testKeyNotUpdatedOnForeignKeyChange() {
		$u = $this->user;
		$u->id = 1;
		$u->details = $this->user_details;
		$this->assertEquals(1, $u->details->users_id);
		$u->details->users_id = 2;
		$this->assertEquals(1, $u->id);
	}

	/**
	 * Belongs to functionality
	 */

	public function testSetOwnerObject() {
		$d = $this->user_details;
		$u = $this->user;
		$u->id = 3;
		$d->user = $u;
		$this->assertEquals(3, $d->user->id);
	}

	public function testKeyUpdatedOnCreateRelationship() {
		$d = $this->user_details;
		$u = $this->user;
		$u->id = 3;
		$d->user = $u;
		$this->assertEquals(3, $d->users_id);
	}

	public function testKeyUpdatedOnForeignKeyChange() {
		$d = $this->user_details;
		$u = $this->user;
		$u->id = 3;
		$d->user = $u;
		$u->id = 2;
		$this->assertEquals(2, $d->users_id);
	}

	public function testRelationshipExistsWhenNotChildren() {
		$d = $this->user_details;
		$d->users_id = 3;
		$u = $this->user;
		$u->id = 2;
		$this->assertEquals(2, $d->users_id);
	}	

}
?>

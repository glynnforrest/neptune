<?php

namespace neptune\database\relations;

use neptune\database\Thing;
use neptune\database\relations\OneToOne;

require_once dirname(__FILE__) . '/../../test_bootstrap.php';

class User extends Thing {

	protected static $table = 'users';
	protected static $fields = array('id', 'username');
	protected static $relations = array(
		 'details' => array(
			  'type' => 'has_one',
			  'key' => 'id',
			  'other_key' => 'users_id',
			  'other_class' => 'neptune\\database\\relations\\UserDetails'
		 )
	);

}

class UserDetails extends Thing {

	protected static $table = 'user_details';
	protected static $fields = array('id', 'users_id', 'details');

}

/**
 * OneToOneTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class OneToOneTest extends \PHPUnit_Framework_TestCase {

	protected $user;
	protected $user_details;


	public function setUp() {
		$this->user = new User('db');
		$this->user_details = new UserDetails('db');
		$r = new OneToOne('id', get_class($this->user), 'users_id',
			get_class($this->user_details));
		$this->user->addRelation('details', 'id', $r);
		$this->user_details->addRelation('user', 'users_id', $r);
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

	public function testForeignKeyUpdatedOnSetRelation() {
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

	public function testKeyUpdatedOnCreateRelation() {
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

	public function testRelationExistsWhenNotChildren() {
		$d = $this->user_details;
		$d->users_id = 3;
		$u = $this->user;
		$u->id = 2;
		$this->assertEquals(2, $d->users_id);
	}	

}
?>

<?php
namespace neptune\helpers;

class Bitmask {

	protected $bitmask;

	public function __construct($bitmask = 0) {
		$this->bitmask = $bitmask;
	}

	public function getBitmask() {
		return $this->bitmask;
	}

	public function setBitmask($bitmask) {
		$this->bitmask = $bitmask;
	}

	public function hasProperty($property) {
		return ($this->bitmask & $property) === $property;
	}

	public function addProperty($property) {
		$this->bitmask = $this->bitmask | $property;
	}

	public function removeProperty($property) {
		$this->bitmask = $this->bitmask ^ $property;
	}
}

?>

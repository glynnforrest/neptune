<?php

namespace Neptune\View;

use Neptune\View\View;
use Neptune\Exceptions\FileException;
use \SplFileObject;

/**
 * Skeleton
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Skeleton extends View {

	protected $namespace;

	/**
	 * Set the namespace for the class created by this Skeleton.
	 *
	 * @param string $namespace The namespace for this Skeleton.
	 */
	public function setNamespace($namespace) {
		$this->namespace = $namespace;
		return $this;
	}

	/**
	 * Set the namespace for the class created by this Skeleton.
	 *
	 * @param string $namespace The namespace for this Skeleton.
	 */
	public function getNamespace() {
		return $this->namespace;
	}

	/**
	 * Save this skeleton to $file.
	 * A FileException will be thrown if the target file exists unless
	 * $overwrite is true.
	 */
	public function save($file, $overwrite = false) {
		if(!$overwrite && file_exists($file)) {
			throw new FileException("File exists: $file");
		}
		$f = new \SplFileObject($file, 'w');
		$f->fwrite('<?php' . PHP_EOL);
		$f->fwrite($this->render());
	}

}

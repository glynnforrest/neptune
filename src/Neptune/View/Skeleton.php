<?php

namespace Neptune\View;

use Neptune\View\View;
use Neptune\Core\Config;
use Neptune\Exceptions\FileException;
use \SplFileObject;

/**
 * Skeleton
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Skeleton extends View {

	protected $namespace;

	protected function __construct() {
	}

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
	 * Save the currrently loaded skeleton to $file.
	 * A FileException will be thrown if the target file exists unless
	 * $overwrite is true.
	 */
	public function saveSkeleton($file, $overwrite = false) {
		if(!$overwrite && file_exists($file)) {
			throw new FileException("File exists: $file");
		}
		$f = new \SplFileObject($file, 'w');
		$f->fwrite('<?php' . PHP_EOL);
		$f->fwrite($this->__toString());
	}

	/**
	 * Set the view used for this skeleton.
	 * This overrides the setView method in View to look in the
	 * neptune skeleton directory.
	 */
	public function setView($view, $absolute = false) {
		if(!$absolute) {
			$view = Config::load('neptune')
				->getRequired('dir.neptune') . 'skeletons/' . $view;
		}
		$this->view = $view . self::EXTENSION;
	}

}

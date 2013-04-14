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

    /**
     * Automatically get the namespace of the application and apply it
     * to the view.
     */
    protected function __construct() {
        $this->namespace = Config::load('neptune')->getRequired('namespace');
    }

    /**
     * Save the currrently loaded skeleton to $file.
     * A FileException will be thrown if the target file exists unless
     * $overwrite is true.
     */
    public function saveSkeleton($file, $overwrite = false) {
        if(!$overwrite && is_file($file)) {
            throw new FileException("File exists: $file");
        }
        $f = new \SplFileObject($file, 'w');
        $f->fwrite('<?php' . PHP_EOL);
        $f->fwrite($this->__toString());
    }

    public static function load($skeleton) {
        /* $me = parent::loadAbsolute($skeleton);
         return $me; */
        //TODO
    }


}

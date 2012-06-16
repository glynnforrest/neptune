<?php
namespace neptune\controller;

use neptune\controller\Controller;
use neptune\assets\Asset;
use neptune\assets\Assets;
use neptune\exceptions\FileException;
use neptune\core\Config;

/**
 * AssetsController
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetsController extends Controller {

	protected static $filters = array();
	protected $current_prefix;

	protected function _before() {
		//register all of neptune's built in filters here.
		// self::registerFilter('filter', 'neptune\\filter_name');
		return true;
	}

	public static function registerFilter($name, $class_name) {
		self::$filters[$name] = $class_name;
	}

	protected function applyFilter(&$asset, $filter) {
		if(!isset($this->filters[$filter])) {
			return false;
		}
		$filter = new $this->filters[$filter];
		$filter->filterAsset($asset);
		return true;
	}


	public function serveAsset($asset) {
		$asset = urldecode($asset) . '.' . $this->request->format();
		$asset = $this->processPrefix($asset);
		try {
			$a = new Asset($this->getAssetPath($asset));
			// foreach($this->getAssetFilters($asset) as $f) {
			// 	$f->filterAsset($a);
			// }
			$this->response->setFormat($this->request->format());
			return $a->getContent();
		} catch (FileException $e) {
			$this->response->setStatusCode('404');
			return false;
		}
	}

	protected function processPrefix($name) {
		$pos = strpos($name, '#');
		if($pos) {
			$this->current_prefix = substr($name, 0, $pos) . '#';
			$name = substr($name, $pos + 1);
		} else {
			$this->current_prefix = '';
		}
		return $name;
	}

	public function getAssetPath($filename) {
		return Config::get($this->current_prefix . 'assets.dir') . $filename;
	}

	public function getAssetFilters($filename) {
		$filters = Config::get($this->current_prefix . 'assets.filters');
		if(is_array($filters) && !empty($filters)) {
			$matched = array();
			foreach ($filters as $k => $v) {
				if(preg_match($k, $filename)){
					$matched[] = $v;
				}
			}
			return $matched;
		}
	}

}
?>

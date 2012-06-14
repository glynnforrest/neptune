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
		try {
			$a = new Asset($this->getAssetFileName($asset));
			// foreach($this->getAssetFilters($asset) as $f) {
			// 	$f->filterAsset($a);
			// }
			$this->response->setFormat('css');
			return $a->getContent();
		} catch (FileException $e) {
			echo $e;
			$this->response->setStatusCode('404');
			return false;
		}
	}

	public function getAssetFileName($name) {
		$pos = strpos($name, '#');
		if($pos) {
			$this->current_prefix = substr($name, 0, $pos) . '#';
			$name = substr($name, $pos + 1);
		} else {
			$this->current_prefix = '';
		}
		return Config::get($this->current_prefix . 'assets.dir') . $name;
	}

	protected function getAssetFilters($name) {
		//return an array of filters to apply to the requested asset.
		//run name through prefix#assets.filters array, check if they match a 
		//regex, if so include those filters.
	}

}
?>

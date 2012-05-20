<?php
namespace neptune\controller;

use neptune\controller\Controller;
use neptune\assets\Asset;
use neptune\assets\Assets;
use neptune\exceptions\FileException;

/**
 * AssetsController
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetsController extends Controller {

	public function serveAsset($asset) {
		try {
			// Assets::getInstance()->registerFilter('foo', 'sandbox\\Foo');
			$a = new Asset($this->getAssetFileName($asset));
			foreach($this->getAssetFilters($asset) as $filter) {
				$a->addFilter($filter);
			}
			$a->filterContent();
			$this->response->setFormat('css');
			return $a->getContent();
		} catch (FileException $e) {
			$this->response->setStatusCode('404');
			return false;
		}
	}

	protected function getAssetFileName($name) {
		return '/tmp/asset.css';
		$pos = strpos($source, '#');
		if($pos) {
			// $this->prefix = substr($source, 0, $pos);
			return substr($source, $pos + 1);
		} else {
			// $this->prefix = '';
			return $source;
		}
	}

	protected function getAssetFilters($name) {
		// return array('foo');
		//return an array of filters to apply to the requested asset.
	}

}
?>

<?php
namespace Neptune\Controller;

use Neptune\Controller\Controller;
use Neptune\Assets\Asset;
use Neptune\Assets\Assets;
use Neptune\Exceptions\FileException;
use Neptune\Core\Config;

/**
 * AssetsController
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetsController extends Controller {

	protected static $filters = array();
	protected $current_prefix;

	protected function _before() {
		//register all of neptune's built in filters here.
		// self::registerFilter('filter', 'Neptune\\FilterName');
		return true;
	}

	/**
	 * Make an asset filter available to AssetsController.
	 * $name is the name of the filter when used in configuration files.
	 * $class_name is the fully qualified name to the filter class.
	 */
	public static function registerFilter($name, $class_name) {
		self::$filters[$name] = $class_name;
	}

	protected function applyFilter(&$asset, $filter) {
		if(!isset(self::$filters[$filter])) {
			throw new \Exception("Asset filter $filter has not been registered with AssetsController.");
		}
		$filter = new self::$filters[$filter];
		$filter->filterAsset($asset);
		return true;
	}


	public function serveAsset($asset_name) {
		//decode url characters, we need &23 to #
		$asset_name = urldecode($asset_name) . '.' . $this->request->format();
		//use the correct config file for this asset by looking at the
		//prefix. This is in the form prefix#asset
		$asset_name = $this->processPrefix($asset_name);
		try {
			$asset = new Asset($this->getAssetPath($asset_name));
			foreach($this->getAssetFilters($asset_name) as $f) {
				$this->applyFilter($asset, $f);
			}
			$this->response->setFormat($this->request->format());
			return $asset->getContent();
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
		return Config::load()->get($this->current_prefix . 'assets.dir') . $filename;
	}

	/**
	 * Get all filters that should be applied to $filename.
	 *
	 * This will look in 'assets.filters' for a list of regular
	 * expressions to test $filename against. If $filename matches,
	 * filter will be run on the asset.
	 *
	 * Example assets.filters:
	 *
	 *	'filters' => array(
	 *	'`.*\.js$`' => 'minifyjs',
	 *	'`.*\.css$`' => 'minifycss\filter'
	 *	)
	 */
	public function getAssetFilters($filename) {
		//list of filters that have matched.
		$matched = array();
		//grab the regexps to test against from the config file for this asset
		$regexps = Config::load()->get($this->current_prefix . 'assets.filters');
		if(is_array($regexps) && !empty($regexps)) {
			foreach ($regexps as $regex => $filter_string) {
				//check that $filename matches this regex
				if(preg_match($regex, $filename)){
					//$filename matches, but $filter_string can
					//contain more than one filter, separated by
					//|. Split $filter_string into seperate filters
					//and add to the matched list.
					foreach (explode('|', $filter_string) as $filter) {
						$matched[] = $filter;
					}
				}
			}
		}
		return $matched;
	}

}

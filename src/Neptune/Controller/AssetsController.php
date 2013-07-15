<?php
namespace Neptune\Controller;

use Neptune\Controller\Controller;
use Neptune\Assets\Asset;
use Neptune\Assets\Filter;
use Neptune\Assets\Assets;
use Neptune\Core\Config;

/**
 * AssetsController
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetsController extends Controller {

	protected static $filters = array();
	//array of options that are passed to a filter on instantiation.
	protected static $filter_options = array();
	protected $current_prefix;

	protected function _before() {
		//register all of neptune's built in filters here.
		// self::registerFilter('filter', 'Neptune\\FilterName');
		return true;
	}

	/**
	 * Make an asset filter available to AssetsController.
	 *
	 * $name is the name of the filter when used in configuration files.
	 *
	 * $class_name is the fully qualified name to the filter class.
	 *
	 * $options is an array of options to pass to the filter on
	 * instantiation, such as the path to a binary.
	 */
	public static function registerFilter($name, $class_name,
										  array $options = array()) {
		self::$filters[$name] = $class_name;
		self::$filter_options[$name] = $options;
	}

	public function applyFilter(Asset &$asset, $filter) {
		if(!isset(self::$filters[$filter])) {
			throw new \Exception("Asset filter $filter has not been registered with AssetsController.");
		}
		$filter_class = self::$filters[$filter];
		$filter_options = self::$filter_options[$filter];
		$filter = new $filter_class($filter_options);
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
			//grab the regexps to test against from the config file for this asset
			$regexps = Config::load($this->current_prefix)->get('assets.filters');
			if(is_array($regexps) && !empty($regexps)) {
				foreach($this->getAssetFilters($asset_name, $regexps) as $f) {
					$this->applyFilter($asset, $f);
				}
			}
			$this->response->setFormat($this->request->format());
			return $asset->getContent();
		} catch (\Exception $e) {
			$this->response->setStatusCode('404');
			return false;
		}
	}

	protected function processPrefix($name) {
		$pos = strpos($name, '/');
		if($pos) {
			$this->current_prefix = substr($name, 0, $pos);
			$name = substr($name, $pos + 1);
		} else {
			$this->current_prefix = '';
		}
		return $name;
	}

	public function getAssetPath($filename) {
		return Config::load($this->current_prefix)->get('assets.dir') . $filename;
	}

	/**
	 * Get all filters that should be applied to $filename.
	 *
	 * $regexps should be an array where keys are regular expressions
	 * to test $filename against and values are the filters to run on
	 * the asset. If $filename matches a key, the filter in the value
	 * will be run on the asset.
	 *
	 * Example $regexps:
	 *
	 *	array(
	 *	'`.*\.js$`' => 'minifyjs',
	 *	'`.*\.css$`' => 'minifycss\addcopyright'
	 *	)
	 */
	public function getAssetFilters($filename, $regexps) {
		//list of filters that have matched.
		$matched = array();
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
		return $matched;
	}

}

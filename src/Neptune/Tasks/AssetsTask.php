<?php

namespace Neptune\Tasks;

use Neptune\Core\Config;
use Neptune\Helpers\String;
use Neptune\Tasks\Task;
use Neptune\Controller\AssetsController;
use Neptune\Assets\Asset;

use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

/**
 * AssetsTask
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AssetsTask extends Task {

	protected $build_dir;

	public function build($prefix = null) {
		$build_dir = $this->getRootDirectory()
			. 'public/'
			. $this->config->get('assets.url');
		//make sure build_dir has a trailing slash
		if(substr($build_dir, -1) !== '/') {
			$build_dir .= '/';
		}
		//create build_dir if it doesn't exist
		if(!file_exists($build_dir)) {
			mkdir($build_dir, 0755, true);
			$this->console->write("Creating $build_dir");
		}
		if(!is_dir($build_dir) | !is_writeable($build_dir)) {
			throw new \Exception(
				"Unable to write to $build_dir. Check file paths and permissions are correct.");
		}
		//set $this->build_dir so it is accessible outside this method
		$this->build_dir = $build_dir;

		if($prefix) {
			$modules = array($prefix);
		} else {
			$modules = $this->config->get('modules');
		}
		foreach ($modules as $module => $src) {
			$this->buildAssetsFromConfig(Config::load($module, $prefix), $module);
		}
	}

	protected function buildAssetsFromConfig(Config $c, $prefix) {
		$src_dir = $c->getRequired('assets.dir');
		$target_dir = $this->build_dir . $prefix . '/';
		if(!file_exists($target_dir)) {
			mkdir($target_dir, 0755, true);
			$this->console->write("Creating $target_dir");
		}

		//create an iterator that recursively loops through the source
		//directory, ignoring dots and listing most shallow paths
		//first
		$i = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$src_dir, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST);

		//loop through all paths. Create a new dir in the target dir, or run it through asset filters from the current config.
		foreach ($i as $file) {
			if ($file->isDir()) {
				if(!file_exists($target_dir . $i->getSubPathName())) {
					mkdir($target_dir . $i->getSubPathName());
				}
			} else {
				$this->processAsset($file, $target_dir .
									$i->getSubPathName(), $c->get('assets.filters'));
			}
			$this->console->write('Creating ' . $target_dir . $i->getSubPathName());
		}
	}

	protected function processAsset($filename, $target, $regexps) {
		$asset = new Asset($filename);
		$c = new AssetsController();
		foreach($c->getAssetFilters($filename, $regexps) as $f) {
			$this->console->write($f);
			$c->applyFilter($asset, $f);
		}
		$asset->saveFile($target);
	}

}
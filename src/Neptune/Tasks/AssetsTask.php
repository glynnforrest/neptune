<?php

namespace Neptune\Tasks;

use Neptune\Core\Config;
use Neptune\Helpers\String;
use Neptune\Tasks\Task;

use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

/**
 * AssetsTask
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AssetsTask extends Task {

	protected $build_dir;

	public function build($build_dir = null, $prefix = null) {
		if(!$build_dir) {
			$build_dir = $this->console->read('Build directory:');
		}
		//make sure build_dir has a trailing slash
		if(substr($build_dir, -1) !== '/') {
			$build_dir .= '/';
		}
		if(!file_exists($build_dir)) {
			mkdir($build_dir, 0755, true);
			$this->console->write("Creating $build_dir...");
		}
		if(!is_dir($build_dir) | !is_writeable($build_dir)) {
			throw new \Exception(
				"Unable to write to $build_dir. Check file paths and permissions are correct.");
		}
		//set $this->build_dir so it is accessible outside this method
		$this->build_dir = $build_dir;

		/* $cfg = Config::load('neptune'); */
		if($prefix) {
			$configs = array($prefix);
		} else {
			$configs = array();
		}
		foreach ($configs as $c) {
			$this->buildAssetsFromConfig(Config::load('module', $prefix), 'test');
		}
	}

	protected function buildAssetsFromConfig(Config $c, $prefix) {
		$src_dir = $c->getRequired('assets.dir');
		$target_dir = $this->build_dir . $prefix . '/';
		if(!file_exists($target_dir)) {
			mkdir($target_dir, 0755, true);
			$this->console->write("Creating $target_dir...");
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
									$i->getSubPathName(), $c->get('filters'));
			}
			$this->console->write('Creating ' . $target_dir . $i->getSubPathName());
		}
	}

	protected function processAsset($file, $target, $regexps) {
		copy($file, $target);
	}

}
<?php

namespace Neptune\Command;

use Neptune\Command\Command;
use Neptune\Console\Console;
use Neptune\Core\Config;
use Neptune\Controller\AssetsController;
use Neptune\Assets\Asset;

use Symfony\Component\Console\Input\InputArgument;

use \DirectoryIterator;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

/**
 * AssetsBuildCommand
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AssetsBuildCommand extends Command {

	protected $name = 'assets:build';
	protected $description = 'Apply filters to all assets and copy to the public folder';
	protected $build_dir;
	protected $progress;
	protected $assets_count;

	protected function configure() {
		$this->setName($this->name)
			 ->setDescription($this->description)
			 ->addArgument(
				 'modules',
				 InputArgument::IS_ARRAY,
				 'A list of modules to build instead of all.'
			 );
	}

	protected function setupBuildDir() {
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
			$this->console->writeln("Creating $build_dir");
		}
		if(!is_dir($build_dir) | !is_writeable($build_dir)) {
			throw new \Exception(
				"Unable to write to $build_dir. Check file paths and permissions are correct.");
		}
		$this->build_dir = $build_dir;
	}

	protected function getModulesToProcess() {
		$args = $this->input->getArgument('modules');
		if($args) {
			$modules = array();
			foreach ($args as $name) {
				$modules[$name] = $this->config->getRequired('modules.' . $name);
			}
		} else {
			$modules = $this->config->getRequired('modules');
		}
		return $modules;
	}

	public function go(Console $console) {
		$this->setupBuildDir();
		//create shared assets controller instance
		/* $this->assets_controller = new AssetsController(); */

		$modules = $this->getModulesToProcess();
		//create dirs
		$assets = $this->getAssets($modules);

		$this->assets_count = count($assets);

		//check for dry run argument here

		//create the progress bar, but only if output is not very verbose
		if(!$this->output->isVeryVerbose()) {
			$this->progress = $this->getHelper('progress');
			$this->progress->start($this->output, $this->assets_count);
			$this->progress->setEmptyBarCharacter('_');
			$this->progress->setBarCharacter('-');
			$this->progress->setProgressCharacter('€');
		}

		//each asset is an array:
		//array($src, $target, $filters)
		foreach ($assets as $count => $asset) {
			$this->processAsset($asset[0], $asset[1], $asset[2]);
			$this->advance($asset[1], $count);
		}

		if(!$this->output->isVeryVerbose()) {
			$this->progress->finish();
		}
		$console->writeln(sprintf('Built assets to <info>%s</info>', $this->build_dir));
	}

	protected function advance($target, $count) {
		if(!$this->output->isVeryVerbose()) {
			$this->progress->advance();
		} else {
			$percent =  floor($count / $this->assets_count * 100) . '%';
			//pad the percentage so messages line up nicely
			//3%..
			//10%.
			//100%
			$percent = str_pad($percent, 3);
			$this->console->writeln(sprintf('%s Created <info>%s</info>', $percent, $target));
		}
	}

	protected function getAssets(array $modules) {
		$assets = array();
		foreach ($modules as $module => $src) {
			$config = Config::loadModule($module);
			$src_dir = $config->getModulePath('assets.dir');

			//identify the target build dir for this module. Create if
			//necessary.
			$target_dir = $this->build_dir . $module . '/';
			if(!file_exists($target_dir)) {
				mkdir($target_dir, 0755, true);
				$this->console->debug("Created directory <info>$target_dir</info>");
			}

			//get the filters for this module
			$filters = $config->get('asset.filters');

			//create an iterator that recursively loops through the source
			//directory, ignoring dots and listing most shallow paths
			//first
			$i = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(
					$src_dir, RecursiveDirectoryIterator::SKIP_DOTS),
				RecursiveIteratorIterator::SELF_FIRST);

			//loop through all paths. For every directory, create it
			//if it doesn't exist. For every asset, add it to the
			//array of assets with the filters from the current
			//config.
			foreach ($i as $file) {
				if ($file->isDir()) {
					if(!file_exists($target_dir . $i->getSubPathName())) {
						mkdir($target_dir . $i->getSubPathName());
					}
				} else {
					$assets[] = array($file, $target_dir . $i->getSubPathName(), $filters);
				}
			}
		}
		return $assets;
	}

	protected function processAsset($src, $target, $regexps) {
		$asset = new Asset($src);
		$c = new AssetsController();
		//add filters if we have any
		if(is_array($regexps)) {
			foreach($c->getAssetFilters($src, $regexps) as $f) {
				$this->console->write($f);
				$c->applyFilter($asset, $f);
			}
		}
		$asset->saveFile($target);
	}

}

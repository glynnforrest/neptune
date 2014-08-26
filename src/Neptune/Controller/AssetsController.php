<?php
namespace Neptune\Controller;

use Neptune\Controller\Controller;
use Neptune\Assets\Asset;
use Neptune\Assets\Filter;
use Neptune\Assets\Assets;
use Neptune\Core\Neptune;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AssetsController
 * @author Glynn Forrest me@glynnforrest.com
 **/
class AssetsController extends Controller {

    protected $neptune;

    public function __construct(Neptune $neptune)
    {
        $this->neptune = $neptune;
    }

	public function serveAssetAction(Request $request, $asset_name) {
		try {
			$asset = new Asset($this->getAssetPath($asset_name));
			//grab the regexps to test against from the config file for this asset
			$response = new Response();
			$response->setContent($asset->getContent());
			$response->headers->set('Content-Type', $asset->getMimeType());
			$response->headers->set('X-Generated-By', get_class($this));
			$response->headers->set('Content-Length', $asset->getContentLength());
			return $response;
		} catch (\Exception $e) {
			return new Response($e->getMessage(), 404);
		}
	}

    public function getAssetPath($asset)
    {
        //the first segment of the asset name is the module name
        $pos = strpos($asset, '/');
        if($pos) {
            $module = substr($asset, 0, $pos);
            $name = substr($asset, $pos + 1);
        } else {
            return sprintf('%sapp/assets/%s', $this->neptune->getRootDirectory(), $asset);
        }
        return sprintf('%sassets/%s', $this->neptune->getModuleDirectory($module), $name);
    }

}

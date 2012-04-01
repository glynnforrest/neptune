<?php

namespace neptune\view;

use neptune\view\View;
use neptune\format\Json;
use neptune\database\Thing;
use neptune\database\ThingCollection;
use neptune\helpers\String;

/**
 * JsonView
 * @author Glynn Forrest me@glynnforrest.com
 **/
class JsonView extends View {

	public function getPreferredVars() {
		$vars = array();
		foreach($this->vars as $k => $v) {
			if($v instanceof Thing) {
				$v->_type = String::single($v->getTable());
				$vars[$k] = $v->getValues();
			}
			if($v instanceof ThingCollection) {
				$vars[$k] = $v->getValues();
			}
		}
		if(!empty($vars)) {
			return $vars;
		}
		return $this->vars;
	}

	public function render() {
		return Json::create($this->getPreferredVars())->encode();
	}
}
?>

<?php

namespace Neptune\View;

use Neptune\View\View;
use Neptune\Format\Xml;
use Neptune\Database\Thing;
use Neptune\Database\ThingCollection;

use Crutches\Inflector;

/**
 * XmlView
 * @author Glynn Forrest me@glynnforrest.com
 **/
class XmlView extends View {

	public function getPreferredVars() {
		$vars = array();
		$c = 0;
		foreach($this->vars as $k => $v) {
			if($v instanceof Thing) {
				$k = Inflector::locale()->single($v->getTable()) . '#' . $c;
				$vars[$k] = $v->getValues();
				$c++;
			}
			if($v instanceof ThingCollection) {
				$k = $v->getTable() . '#' . $c;
				$results = $v->getValues();
				$data = array();
				$d = 0;
				$table = Inflector::locale()->single($v->getTable()) . '#';
				foreach($results as $result) {
					$data[$table . $d] = $result;
					$d++;
				}
				$vars[$k] = $data;
				$c++;
			}
		}
		if(!empty($vars)) {
			return $vars;
		}
		return $this->vars;
	}


	public function render() {
		return Xml::create($this->getPreferredVars())->encode();
	}
}
?>

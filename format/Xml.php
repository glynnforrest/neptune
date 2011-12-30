<?php
namespace neptune\format;

use \XmlWriter;

/**
 * Xml
 * @author Glynn Forrest me@glynnforrest.com
 **/
class Xml extends Format {

	protected $default_tag = 'node';

	public function encode() {
		$xml = new XmlWriter();
		$xml->openMemory();
		$xml->startDocument('1.0', 'UTF-8');
		$xml->startElement('xml');
		if(count($this->content) === 1) {
			$content = reset($this->content);
			$key = key($this->content);
			if(!is_array($content)) {
				if(is_numeric($key)) {
					$key = $this->default_tag;
				}
				$xml->writeElement($key, $content);
			} else {
				$this->array_to_xml($content, $xml);
			} 
		} else {
			$this->array_to_xml($this->content, $xml);
		}
		$xml->endElement();
		return $xml->outputMemory();
	}

	public function read($content) {
	
	}

	protected function array_to_xml($content, &$xml) {
		foreach($content as $key => $value) {
			$pos = strpos($key, '#');
			if($pos) {
				$key = substr($key, 0, $pos);
			}
			if(is_array($value)) {
				if(!is_numeric($key)){
					$xml->startElement($key);
					$this->array_to_xml($value, $xml);
					$xml->endElement();
				}
				else{
					$this->array_to_xml(array($this->default_tag => $value), $xml);
				}
			} else {
				if(!is_numeric($key)){
					$xml->writeElement($key, $value);
				} else {
					$xml->writeElement($this->default_tag, $value);
				}
			}
		}
	}

	public function setDefaultTag($name) {
		$this->default_tag = $name;
	}
}
?>

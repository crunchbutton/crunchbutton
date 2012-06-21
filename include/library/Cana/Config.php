<?php

/**
 * Config object
 *
 * @author		Devin Smith <devin@cana.la>
 * @date		2009.10.01
 *
 * The config object is a self loading xml, json, and ini parser. Pass it a file base name and
 * it will construct an object for you to access.
 *
 */


final class Cana_Config {

	private $_root = 'data';
	private $_path;

	/**
	 * Construct the config, parse it, and load it as an object
	 * Accepts xml, json, or ini
	 *
	 */
	public function __construct($config = null, $params = null) {
		if (isset($params['dir'])) {
			$this->_path = $params['dir'];
		}

		if (@file_exists($this->_path.$config)) {
			$this->_contents = file_get_contents($this->_path.$config);
			$this->_type = $this->ext($config);
			switch ($this->_type) {
				case 'json':
					$this->_data = json_decode($this->_contents);
					break;
				case 'yaml':
					$this->_data = yaml_parse($this->_contents);
					break;
				case 'ini':
					$this->_data = parse_ini_string($this->_contents);
					break;
				case 'xml':
					// @todo: convert encoding to allow multibyte				
					$this->_data = $this->parseXml($this->_contents);
					break;
				default:
					$this->_data = [];
					break;
			}
		} else {
			$this->_contents = $config;
			if ($json = @json_decode($this->_contents)) {
				$this->_type = 'json';
				$this->_data = $json;
			} elseif ($xml = $this->parseXml($this->_contents)) {
				$this->_type = 'xml';
				$this->_data = $xml;				
			} else {
				$this->_type = 'ini';
				$this->_data = parse_ini_string($this->_contents);
			}
		}

		$this->_contents = null;

		if (isset($params['append'])) {
			$this->merge($params['append']);
		}

		$this->_data = Cana_Model::toModel($this->_data);

	}
	
	public function merge($data) {
		foreach ($data as $key => $value) {
			$this->_data->{$key} = Cana_Model::toModel($value);
		}
		return $this;
	}

	public function ext($file) {
	return substr(strrchr($file, '.'), 1);
	}

	public function parseXml($xmlstr) {
		$doc = new DOMDocument();
		$doc->loadXML($xmlstr);
		return $this->nodeToArray($doc->documentElement);
	}
	
	public function nodeToArray($node) {
		$output = [];
		switch ($node->nodeType) {
	 	case XML_CDATA_SECTION_NODE:
	 	case XML_TEXT_NODE:
			$output = trim($node->textContent);
	 	break;
	 	case XML_ELEMENT_NODE:
			for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
	 		$child = $node->childNodes->item($i);
	 		$v = $this->nodeToArray($child);
	 		if(isset($child->tagName)) {
	 			$t = $child->tagName;
	 			if(!isset($output[$t])) {
					$output[$t] = [];
	 			}
	 			$output[$t][] = $v;
	 		}
	 		elseif($v) {
				$output = (string) $v;
	 		}
			}
			if (is_array($output)) {
	 		if ($node->attributes->length) {
				$a = [];
				foreach($node->attributes as $attrName => $attrNode) {
	 			$a[$attrName] = (string) $attrNode->value;
				}
				$output['@attributes'] = $a;
	 		}
	 		foreach ($output as $t => $v) {
				if(is_array($v) && count($v)==1 && $t!='@attributes') {
	 			$output[$t] = $v[0];
				}
	 		}
			}
	 	break;
		}
		return $output;
	}

	public function &__get($name) {
		if (isset($name{0}) && $name{0} == '_') {
			return $this->{$name};
		} else {
			return $this->_data->$name;
		}
	}
	
	public function __set($name, $value) {
		if ($name{0} == '_') {
			return $this->{$name} = $value;
		} else {
			return $this->_data->$name = $value;
		}
	}
	
}
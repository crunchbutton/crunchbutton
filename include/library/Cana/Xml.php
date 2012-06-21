<?php
/**
 * Xml parser class
 *
 * @author		Devin Smith <devin@cana.la>
 * @date		2009.10.01
 *
 * The xml parser will either load a file or accept plain text. It will
 * set $_data to the array it was able to parse;
 *
 * DEPRECIATED: The Cana_Config object now has its own domdocument loader.
 * this class can be used if there is no dom module installed.
 *
 */
 
 
class Cana_Xml extends Cana_Model {

	private $_contents;
	private $_data;
	private $_dir;
	private $_xml;
	
	/**
 	* Construct the object with a file or text
 	* 
 	* param	string		The file or data to load
 	* @param	params		Optional paraters
 	* 	dir		string		the dir to look for the file
 	*/
	public function __construct($xml = null, $params = null) {
		if (isset($xml)) {
			if (@file_exists($params['dir'].$xml)) {
				$this->_contents = file_get_contents($params['dir'].$xml);
			} else  {
				$this->_contents = $xml;
			}
			
			$this->data($this->parse($this->_contents));
			$this->_contents = null;
		}
	}

	/**
 	* Convert the xml contents into an array and return it
 	*
 	* @param	string		The xml text to parse
 	* @param	bool		Get tag attributes
 	* @param	string		Whether or not to prioritize child tags or attributes
 	* @return	array
 	*/
	public function parse($contents, $get_attributes = 1, $priority = 'tag') {
		if (!$contents) return [];
	
		if (!function_exists('xml_parser_create')) {
			throw new Exception('Can not parse xml files because xml_parser_create does not exist!');
			return [];
		}
	
		//Get the XML parser of PHP - PHP must have this module for the parser to work
		$parser = xml_parser_create('');
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xml_values);
		xml_parser_free($parser);
	
		if (!$xml_values) return;
	
		//Initializations
		$xml_array = [];
		$parents = [];
		$opened_tags = [];
		$arr = [];
	
		$current = &$xml_array; //Refference
	
		//Go through the tags.
		$repeated_tag_index = [];//Multiple tags with same name will be turned into an array
		foreach ($xml_values as $data) {
			unset($attributes,$value);//Remove existing values, or there will be trouble
	
			//This command will extract these variables into the foreach scope
			// tag(string), type(string), level(int), attributes(array).
			extract($data);//We could use the array by itself, but this cooler.
	
			$result = [];
			$attributes_data = [];
			
			if (isset($value)) {
				if ($priority == 'tag') $result = $value;
				else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
			}
	
			//Set the attributes too.
			if (isset($attributes) && $get_attributes) {
				foreach($attributes as $attr => $val) {
					if ($priority == 'tag') $attributes_data[$attr] = $val;
					else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				}
			}
	
			//See tag status and do the needed.
			if ($type == 'open') {//The starting of the tag '<tag>'
				$parent[$level-1] = &$current;
				if (!is_array($current) || (!in_array($tag, array_keys($current)))) { //Insert New tag
					$current[$tag] = $result;
					if ($attributes_data) $current[$tag. '_attr'] = $attributes_data;
					$repeated_tag_index[$tag.'_'.$level] = 1;
	
					$current = &$current[$tag];
	
				} else { //There was another element with the same tag name
	
					if (isset($current[$tag][0])) { //If there is a 0th element it is already an array
						$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
						$repeated_tag_index[$tag.'_'.$level]++;

					} else { //This section will make the value an array if multiple tags with the same name appear together
						$current[$tag] = [$current[$tag],$result];//This will combine the existing item and the new item together to make an array
						$repeated_tag_index[$tag.'_'.$level] = 2;
						
						if (isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
							$current[$tag]['0_attr'] = $current[$tag.'_attr'];
							unset($current[$tag.'_attr']);
						}
	
					}
					$last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
					$current = &$current[$tag][$last_item_index];
				}
	
			} elseif($type == 'complete') { //Tags that ends in 1 line '<tag />'
				//See if the key is already taken.
				if(!isset($current[$tag])) { //New Key
					$current[$tag] = $result;
					$repeated_tag_index[$tag.'_'.$level] = 1;
					if ($priority == 'tag' && $attributes_data) $current[$tag. '_attr'] = $attributes_data;
	
				} else { //If taken, put all things inside a list(array)
					if (isset($current[$tag][0]) && is_array($current[$tag])) {//If it is already an array...
	
						// push the new element into that array.
						$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
						
						if ($priority == 'tag' && $get_attributes && $attributes_data) {
							$current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
						}
						$repeated_tag_index[$tag.'_'.$level]++;
	
					} else { //If it is not an array
						$current[$tag] = [$current[$tag],$result]; //...Make it an array using using the existing value and the new value
						$repeated_tag_index[$tag.'_'.$level] = 1;

						if ($priority == 'tag' && $get_attributes) {
							if (isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
								
								$current[$tag]['0_attr'] = $current[$tag.'_attr'];
								unset($current[$tag.'_attr']);
							}
							
							if ($attributes_data) {
								$current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
							}
						}
						$repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
					}
				}
	
			} elseif($type == 'close') { //End of tag '</tag>'
				$current = &$parent[$level-1];
			}
		}
		
		return $xml_array;
	}
	
	
	/**
 	* Self returning accessor/mutators
 	*/
	public function data($data = null) {
		if (isset($data)) {
			$this->_data = $data;
			return $this;
		} else {
			return $this->_data;
		}
	}

} 
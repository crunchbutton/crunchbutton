<?php

/**
 * The view model
 * 
 * @author		Devin Smith <devin@cana.la>
 * @date		2009.09.17
 *
 * The view model include the phtml files and executes them.
 * Since the phtml files are included from the view object, we can access
 * it by calling $this to access view variables/functions.
 * The phtmls require short tags to be enabled.
 *
 */


class Cana_View extends Cana_Model {
	private $_layout = 'layout/html';
	public $headers;
	public $pageLinks;
	private $_rendering = false;
	private $_useFilter = true;
	private $_theme;
	private $_base;
	
	public function __construct ($params = []) {
		$this->headers 			= new Cana_Model;
		$this->headers->http 	= new Cana_Model;
		$this->headers->script 	= new Cana_Model;
		$this->headers->style 	= new Cana_Model;
		$this->pageLinks 		= new Cana_Model;
		
		if (isset($params['layout'])) {
			$this->layout($params['layout']);
		}
		
		if (isset($params['theme'])) {
			$this->theme($params['theme']);
		}
		
		if (isset($params['base'])) {
			$this->_base = $params['base'];
		}
		
		if (isset($params['httpHeaders'])) {
			$this->headers->http = $params['httpHeaders'];
		}
	}
	

	/**
	 * Include the file inside an output buffer, and return its contenrts
	 *
	 * @param	string		the file to include
	 * @param	string		options
	 *	var		string		set an intervar of this name to the rendered contents
	 *	set		array		an array of key value pairs to set internal values for
	 *	display	bool		whether or not to show the layout wrapper
	 *	filter	bool		whether or not to filter whitespace from the render
	 * @return	string
	 */
	public function render($view, $params = null) {
		if (isset($params['set'])) {
			foreach ($params['set'] as $key => $value) {
				$$key = $value;
			}
		}
		
		$theme = $this->theme();
		$theme = array_reverse($theme);

		foreach ($theme as $dir) {
			if (file_exists($this->_base.$dir.$view.'.phtml')) {
				$file = $this->_base.$dir.$view.'.phtml';
				break;
			}
		}
		if (!isset($file)) {
			throw new Exception('Could not find: '.$view);
		}

		foreach ($theme as $dir) {
			if (file_exists($this->_base.$dir.$this->layout().'.phtml')) {
				$layout = $this->_base.$dir.$this->layout().'.phtml';
				break;
			}
		}

		if ($this->_rendering || !isset($params['display'])) {
			
			ob_start();
			include($file);
			$page = $this->outputFilter(ob_get_contents(),$params);
			ob_end_clean();
			
		} else {
			
			$this->_rendering = true;
			ob_start();
			include($file);
			$this->content = $this->outputFilter(ob_get_contents(),$params);
			ob_end_clean();

			ob_start();
			include($layout);
			$page = $this->outputFilter(ob_get_contents(),$params);
			ob_end_clean();
			$this->_rendering = false;
		}		
		
		if (isset($params['var'])) {
			$this->{$params['var']} = $page;
		}
		return $page;	
	}
	
	
	/**
	 * Output the contents of the view after rendering it. If headers have
	 * not been sent, we will send all our view headers.
	 *
	 * @param	string		the view file to display
	 */
	public function display($view,$params=null) {
		if (!headers_sent()) {
			foreach ($this->headers->http as $key => $value) {
				header(isset($value['name']) ? $value['name'].': ' : '' . $value['value'],isset($value['replace']) ? $value['replace'] : true);
			}
		}
		if (is_null($params)) {
			$params['display'] = true;
		}
		echo $this->render($view,$params);
	}
	
	
	/**
	 * Filter whitespace to remove unwanted spaces.
	 *
	 * @param	string	the content to pass
	 * @param	array	array of config values
	 *	filter	bool	whether or not to run the filter (overwrite object var)
	 * @return	string
	 */
	private function outputFilter($content, $params) {
		if ((isset($params['filter']) && $params['filter']) || (!isset($params['filter']) && $this->_useFilter != false)) {
			$find = [
				'/^(\s?)(.*?)(\s?)$/',
				'/\t|\n|\r/',
				'/(\<\!\-\-)(.*?)\-\-\>/'
			];
			$replace = [
				'\\2',
				'',
				''
			];
			return preg_replace($find,$replace,$content);
		} else {
			return $content;
		}
	}
	
	
	/**
	 * Accessor methods
	 */
	public function layout($value = null) {
		if (!is_null($value)) {
			$this->_layout = $value;
			return $this;
		} else {
			return $this->_layout;
		}
	}
	
	public function theme($value = null) {
		if (!is_null($value)) {
			$this->_theme = $value;
			return $this;
		} else {
			return $this->_theme;
		}
	}
	
	public function themeStack($value) {
		$this->_theme[] = $value;
	}
	
	public function useFilter($filter = null) {
		if (!is_null($filter)) {
			$this->_useFilter = $filter;
			return $this;
		} else {
			return $this->_filter;
		}
	}
	
	public function helper($helper, $params = []) {
		if (isset($this->_helper[$helper])) {
			return $this->_helper[$helper];
		} else {
			$className = 'Cana_View_Helper_'.$helper;
			$this->_helper[$helper] = new $className($params);
			return $this->_helper[$helper];
		}
	}

}
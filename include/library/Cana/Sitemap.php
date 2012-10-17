<?php
/**
 * Sitemap class
 * 
 * @author	Devin Smith
 * @date	2010.01.13
 *
 * This is a basic class with google sitemaps integration
 *
 */
 

class Cana_Sitemap extends Cana_Model {
	private $_content;
	private $_baseUrl;
	
	public function __construct($params = null) {
		if (isset($params['baseurl'])) {
			$this->_baseUrl = $params['baseurl'];		
		} else {
			$this->_baseUrl = c::config()->baseUrl->{c::env()};
		}
	}
	
	public function addItem($params) {
		if (isset($params['loc'])) {
			$content = '<url>';
			
			if (!isset($params['lastmod'])) {
				$params['lastmod'] = date('Y-m-d');
			}
			if (!isset($params['changefreq'])) {
				$params['changefreq'] = 'monthly';
			}
			if (!isset($params['priority'])) {
				$params['priority'] = '0.5';
			}
			
			foreach ($params as $key => $value) {
				if ($key == 'loc' && !isset($params['options']['nobase'])) {
					$value = $this->_baseUrl.$value;
				}
				if ($key != 'options') {
					$content .= '<'.$key.'>'.$value.'</'.$key.'>';
				}
			}
			$content .= '</url>';
			$this->_content .= $content;
		}
	}
	
	public function content() {
		return '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$this->_content.'</urlset>';
	}
	
	public function output() {
		header('content-type: text/xml');
		echo $this->content();
	}
}
<?php

/**
 * A simple cli imagemagick wrapper for those who dont compile with im
 *
 * @author	Devin Smith <devin@cana.la>
 * @date	2003.07.04
 *
 * Basic thumbnail generator. Accepts parameters and sends them to the command line.
 * Images are then read from cache rather than being generated on each page load.
 *
 */

class Cana_Thumb extends Cana_Model {
	private $_path;
	private $_cache;
	private $_watermark;
	private $_img;
	private $_im;
	
	
	public function __construct($params = []) {
		$this->setOptions($params);

		// a little checking since php often doesnt properly have include paths set up on http
		if (file_exists('/usr/local/bin/convert')) {
			$this->_im = '/usr/local/bin/convert';
		} elseif(file_exists('/usr/bin/convert')) {
			$this->_im = '/usr/bin/convert';
		} elseif(file_exists('/opt/local/bin/convert')) {
			$this->_im = '/opt/local/bin/convert';
		} else {
			throw new Exception('Could not find imagemagick');
		}
		
		if ($this->_im && isset($params['img'])) {
			$this->writeThumb($params['img']);
		}
	}
	
	
	public function setOptions($params = []) {
		if (isset($params['path'])) {
			$this->_path = $params['path'];
		} elseif (!isset($this->_path)) {
			$this->_path = realpath(dirname(__FILE__)).'/../';
		}
		
		if (isset($params['cache'])) {
			$this->_cache = $params['cache'];
		} elseif (!isset($this->_cache)) {
			$this->_cache = realpath(dirname(__FILE__)).'/_cache/';
		}
		
		if (!file_exists($this->_cache)) {
			throw new Exception('Cache directory "'.$this->_cache.'" does not exist.');
		}
		
		if (isset($params['watermarkSrc'])) {
			$this->_watermarkSrc = $params['watermarkSrc'];
		} elseif (!isset($this->_watermarkSrc)) {
			$this->_watermarkSrc = '../watermark.png';
		}

		if (isset($params['maxsize'])) {
			$this->_maxsize = intval($params['maxsize']);
		} elseif (!isset($this->_maxsize)) {
			$this->_maxsize = 1200;
		}
		
		if (isset($params['width'])) {
			$this->_width = intval($params['width']);
		} elseif (!isset($this->_width)) {
			$this->_width = 0;
		}
		
		if (isset($params['height'])) {
			$this->_height = intval($params['height']);
		} elseif (!isset($this->_height)) {
			$this->_height = 0;
		}
		
		if (isset($params['format'])) {
			$this->_format = $params['format'];
		} elseif (!isset($this->_format)) {
			$this->_format = 'jpg';
		}
		
		if (isset($params['gravity'])) {
			$this->_gravity = $params['gravity'];
		} elseif (!isset($this->_gravity)) {
			$this->_gravity = 'center';
		}
		
		if (isset($params['quality'])) {
			$this->_quality = intval($params['quality']);
		} elseif (!isset($this->_quality)) {
			$this->_quality = 75;
		}
		
		if (isset($params['rounded'])) {
			$this->_rounded = $params['rounded'];
		} elseif (!isset($this->_rounded)) {
			$this->_rounded = 0;
		}
		
		if (isset($params['crop'])) {
			$this->_crop = $params['crop'];
		} elseif (!isset($this->_crop)) {
			$this->_crop = 0;
		}
		
		if (isset($params['bw'])) {
			$this->_bw = $params['bw'] ? true : false;
		} elseif (!isset($this->_bw)) {
			$this->_bw = false;
		}
		
		if (isset($params['watermark'])) {
			$this->_watermark = $params['watermark'] ? true : false;
		} elseif (!isset($this->_watermark)) {
			$this->_watermark = false;
		}
		
		if (isset($params['refresh'])) {
			$this->_refresh = $params['refresh'] ? true : false;
		} elseif (!isset($this->_refresh)) {
			$this->_refresh = false;
		}
		
		if (isset($params['text'])) {
			$this->_text = $params['text'];
		} elseif (!isset($this->_text)) {
			$this->_text = false;
		}
		
		if (isset($params['pdfpage'])) {
			$this->_pdfpage = intval($params['pdfpage']);
		} elseif (!isset($this->_pdfpage)) {
			$this->_pdfpage = false;
		}

		if (!file_exists($this->_watermarkSrc) && $this->_watermark) {
			throw new Exception('Watermark image  "'.$this->_watermarkSrc.'" does not exist.');
		}
	}
	
	
	public function writeThumb($img, $params = []) {
		$this->setOptions($params);
		
		$image['src'] = $this->_path.$img;
		if (!file_exists($image['src'])) {
			throw new Exception('Source file "'.$image['src'].'" does not exist');
			
		} else {

			$image['width'] = $this->_width;
			$image['height'] = $this->_height;
			
			$image['info'] = getimagesize($image['src']);
			if (!$image['info'][0] || !$image['info'][1]) {
				$image['info'][0] = $this->_width;
				$image['info'][1] = $this->_height;
			}
			
			if ($image['width'] == 0 || $image['height'] == 0 || !$image['width'] || !$image['height'] || $image['width'] > $this->_maxsize || $image['height'] > $this->_maxsize || ($image['width'] > $image['info'][0] && $image['height'] > $image['info'][1])) {
			    $image['width'] = $image['info'][0];
			    $image['height'] = $image['info'][1];
			}

			$image['file'] = $this->createFileName($image);
			
			if ($this->_refresh) {
				unlink($image['file']);
			}
			
			if (!file_exists($image['file']) || $this->_refresh || (file_exists($image['file']) && filemtime($image['file']) < filemtime($image['src']))) {
				$out = $this->buildImCmd($image);
			}
			
			if (!file_exists($image['file'])) {
				throw new Exception("Failed to write file.\n".print_r($out,1));
			}
			$this->_image = $image;
		}
		
		return [
			'out' 		=> isset($out) ? $out : null,
			'file' 		=> $image['file']
		];

	}
	
	
	public function createFileName($image) {
		return $this->_cache.md5($image['src'].$image['height'].$image['width'].$this->_rounded.$this->_crop.$this->_bw.$this->_watermark.$this->_text).'.'.$this->_format;
	}
	
	public function getFileName() {
		return basename($this->_image['file']);
	}
	
	
	public function buildImCmd($image) {
		$cmd = $this->_im;
		
		// height and width
		$cmd .= ' -size '.$image['info'][0].'x'.$image['info'][1].' '.escapeshellarg($image['src'].($this->_pdfpage !== false ? '['.$this->_pdfpage.']' : '')).' -thumbnail '.$image['width'].'x'.$image['height'];
		
		// crop resizing
	    if ($this->_crop) {
	    	$cmd.= '^ -gravity '.$this->_gravity.' -extent '.$image['width'].'x'.$image['height'];
	    }

	    // rounded corners
	    if ($this->_rounded) {
			$cmd .= ' \( +clone  -threshold -1 -draw \'fill black polygon 0,0 0,'.$this->_rounded.' '.$this->_rounded.',0 fill white circle '.$this->_rounded.','.$this->_rounded.' '.$this->_rounded.',0\' \( +clone -flip \) -compose Multiply -composite \( +clone -flop \) -compose Multiply -composite \) +matte -compose CopyOpacity -composite';
	    }
	    
	    // black and white
	    if ($this->_bw) {
	    	$cmd .= ' -colorspace Gray';
	    }
	    
	    // watermark
	    if ($this->_watermark) {
	    	$cmd .= ' '.$this->_watermarkSrc.' -compose Plus -gravity southeast -composite';
	    }
	    
	    //jpg compression
	    if ($this->_format == 'jpg') {
	    	$cmd .= ' quality '.$this->_quality;
		}
	    
		// text
		if ($this->_text) {
			$size = $image['height']/3.2;
			$cmd .= ' -fill "#0008" -draw "rectangle 0,'.($image['height']+$size).','.($image['width']).','.($image['height']-$size*1.15).'"';
			$cmd .= ' +repage -pointsize '.$size.' -kerning '.(floor($size/9)).' -font Arial -fill "#dddf" -gravity south -annotate 0 "'.$this->_text.'"';
		}
	
	    // output filename
		$cmd .= ' ' .escapeshellarg($image['file']);
		
		$cmd = 'PATH=$PATH:/usr/local/bin && '.$cmd;

	    exec($cmd.' 2>&1', $out, $err);

	    return [
	    	'out' => $out,
	    	'cmd' => $cmd
	    ];
	}
	
	
	public function displayThumb($image = null) {
		if (is_null($image)) {
			$image = $this->_image;
		}
		if (($image['fileinfo'] = getimagesize($image['file'])) != false) {
		
			// set the headers to look like an image
			header('HTTP/1.1 200 OK');
			header('Date: '.date('r'));
			header('Last-Modified: '.date('r',filemtime($image['file'])));
			header('Accept-Ranges: bytes');
			header('Content-Length: '.filesize($image['file']));
		
		    if ($image['fileinfo']['mime']) {
		        header('Content-type: '.$image['fileinfo']['mime']);
		    } else {
		        header('Content-type: image/jpg');
		    }
		    
		    readfile($image['file']);
		    exit;
		    
		} else {
			header('HTTP/1.0 404 Not Found');
		    exit;
		}
	}

}


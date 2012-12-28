<?

class Controller_assets_css_bundle_css extends Crunchbutton_Controller_AssetBundle {
	public function init() {
		$cacheid = 'crunchr-bundle-node-css'.$_REQUEST['v'];
		
		if (c::app()->cache()->cached($cacheid) && !$_REQUEST['nocache']) {
			$css = c::app()->cache()->read($cacheid);
			$mtime = c::cache()->mtime($cache);
		} else {
	
			$file = c::config()->dirs->www.'assets/css/style.css';
			$css = file_get_contents($file);
			
			if (strpos($_SERVER['HTTP_USER_AGENT'],'Windows') !== false) {
				$file = c::config()->dirs->www.'assets/css/windows.css';
				$css .= file_get_contents($file);
			}
	
			$callback = function($matches) {
				$img = new ImageBase64(c::config()->dirs->www.$matches[1]);
				return 'url('.$img->output().')';
			};
			
			//$css = preg_replace_callback('/url\(([a-z0-9\/\-_\.]+)\)/i',$callback, $css);
			$css = preg_replace('/\t|\n/','',$css);	
			$mtime = filemtime($file);
			c::cache()->write($cache, $css);
			c::app()->cache()->write($cacheid, $css);
		}
		
		/*
		$headers = apache_request_headers();
		if (isset($headers['If-Modified-Since'])) {
			//header('Last-Modified: '.gmdate('D, d M Y H:i:s',$mtime).' GMT', true, 304);
			//exit;
		}
		*/

		header('HTTP/1.1 200 OK');
		header('Date: '.date('r'));
		header('Last-Modified: '.gmdate('D, d M Y H:i:s',$mtime).' GMT');
		header('Accept-Ranges: bytes');
		header('Content-Length: '.strlen($css));
		header('Content-type: text/css');
		
		echo $css;
		exit;
	}
}
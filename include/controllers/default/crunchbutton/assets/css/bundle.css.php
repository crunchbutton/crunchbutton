<?

class Controller_assets_css_bundle_css extends Crunchbutton_Controller_AssetBundle {
	public function init() {
		$file = c::config()->dirs->www.'assets/css/style.css';
		$css = file_get_contents($file);

		$callback = function($matches) {
			$img = new ImageBase64(c::config()->dirs->www.$matches[1]);
			return 'url('.$img->output().')';
		};
		
		$cache = 'dynamic-css-'.md5($_REQUEST['v']).'.css';
		if (c::cache()->cached($cache) && !$_REQUEST['nocache']) {
			$css = c::cache()->read($cache);
			$mtime = c::cache()->mtime($cache);
		} else {
			$css = preg_replace_callback('/url\(([a-z0-9\/\-_\.]+)\)/i',$callback,$css);
			$css = preg_replace('/\t|\n/','',$css);

			$mtime = filemtime($file);

			c::cache()->write($cache, $css);
		}
		


		
		$headers = apache_request_headers();

		if (isset($headers['If-Modified-Since'])) {
			//header('Last-Modified: '.gmdate('D, d M Y H:i:s',$mtime).' GMT', true, 304);
			//exit;
		}

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
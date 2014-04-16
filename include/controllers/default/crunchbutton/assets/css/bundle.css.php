<?

class Controller_assets_css_bundle_css extends Crunchbutton_Controller_AssetBundle {
	public function init() {
		// $v = $_REQUEST['v'];
		$git = Cana_Util::gitVersion();
		$v = $git ? $git : $_REQUEST['v'];

		$cacheid = 'crunchr-bundle-node-css'.$v;
		
		if (c::app()->cache()->cached($cacheid)) {
			$mtime = c::cache()->mtime($cacheid);$mtime = c::cache()->mtime($cacheid);
			
			$headers = apache_request_headers();
			if (isset($headers['If-Modified-Since']) && !$_REQUEST['nocache']) {
				header('Last-Modified: '.gmdate('D, d M Y H:i:s',$mtime).' GMT', true, 304);
				exit;
			}
			
			$cached = true;
		}
		
		if ($cached && !$_REQUEST['nocache']) {
			$data = c::app()->cache()->read($cacheid);

		} else {

			if ($_REQUEST['s']) {
				switch ($_REQUEST['s']) {
					case 'style':
						$style = ['style'];
						break;
					case 'seven':
						$style = ['seven'];
						break;
					default:
						$style = [];
						break;
				}
			}

			$src = c::view()->render('bundle/bundler.css',['set' => ['style' => $style]]);

			$doc = new DOMDocument('1.0');
			@$doc->loadHTML($src);

			foreach ($doc->getElementsByTagName('link') as $script) {
				if ($script->getAttribute('href')) {
					$files[] = c::config()->dirs->www.preg_replace('/^(.*)(\?.*)$/','\\1',$script->getAttribute('href'));
				}
			}

			foreach ($doc->getElementsByTagName('style') as $script) {
				$code = $script->nodeValue;
				$tmp = tempnam('/tmp',$cacheid);
				$tmps[] = $tmp;
				file_put_contents($tmp,$code);
				$files[] = $tmp;
			}

			$data = '';
			foreach ($files as $file) {
				$data .= file_get_contents($file);
			}

			if ($tmps) {
				foreach ($tmps as $tmp) {
					unlink($tmp);
				}
			}

			/*
			$callback = function($matches) {
				$img = new ImageBase64(c::config()->dirs->www.$matches[1]);
				return 'url('.$img->output().')';
			};

			$css = preg_replace_callback('/url\(([a-z0-9\/\-_\.]+)\)/i',$callback, $css);
			*/
			$data = preg_replace('/\t|\n/','',$data);
			$mtime = time();
			c::app()->cache()->write($cacheid, $data);
		}

		/*
		foreach ($data['headers'] as $key => $header) {
			header($key.': '.$header);
		}
		*/

		header('HTTP/1.1 200 OK');
		header('Date: '.date('r'));
		header('Last-Modified: '.gmdate('D, d M Y H:i:s',$mtime).' GMT');
		header('Accept-Ranges: bytes');
		header('Content-Length: '.strlen($data));
		header('Content-type: text/css');
		header('Vary: Accept-Encoding');

		echo $data;
		exit;
	}
}

<?

class Controller_assets_css_bundle_css extends Crunchbutton_Controller_AssetBundle {
	public function init() {
		$v = $_REQUEST['v'] ? $_REQUEST['v'] : getenv('HEROKU_SLUG_COMMIT');

		$id = 'crunchr-bundle-node-css';

		if (preg_match('/ios|iphone|ipad/i',$_SERVER['HTTP_USER_AGENT'])) {
			c::view()->isIOS = true;
		} elseif (preg_match('/android/i',$_SERVER['HTTP_USER_AGENT'])) {
			c::view()->isAndroid = true;
		}

		$this->cacheId($id.$v.$_REQUEST['s'].$_REQUEST['_export'].(c::view()->isIOS ? '1' : '0').(c::view()->isAndroid ? '1' : '0'));
		$this->cacheServe($id);
	}

	public function getData() {
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

		if ($_REQUEST['_export']) {
			c::view()->export = true;
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
			if (preg_match('/\.scss$/',$file)) {
				$data .= Scss::compile($file);
			} else {
				$data .= file_get_contents($file);
			}
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

		return ['mtime' => $mtime, 'data' => $data];
	}
}

<?

class Controller_assets_css_bundle_css extends Crunchbutton_Controller_AssetBundle {
	public function init() {
		$this->cacheServe('crunchr-bundle-node-css');
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

<?

class Controller_assets_js_bundle_js extends Crunchbutton_Controller_AssetBundle {
	public function init() {

		$cacheid = 'crunchr-bundle-node-code'.$_REQUEST['v'].$_REQUEST['s'];
		
		$this->cacheId($cacheid);

		if (Cana::app()->cache()->cached($cacheid)) {
			$data = Cana::app()->cache()->read($cacheid);
		}

		if (!$data || !$data['content']) {

			if ($_REQUEST['s']) {
				switch ($_REQUEST['s']) {
					case 'app':
						$scripts = ['app'];
						break;
					case 'admin':
						$scripts = ['datepicker/eye','datepicker/layout','datepicker/utils','datepicker/datepicker','admin'];
						break;
					case 'cockpit':
						$scripts = [];
						break;
					default:
						$scripts = [];
						break;
				}
			}

			$src = c::view()->render('bundle/bundler.js');

			$doc = new DOMDocument('1.0');
			@$doc->loadHTML($src);

			foreach ($doc->getElementsByTagName('script') as $script) {
				$code = null;
				$src = $script->getAttribute('src');
				if ($src) {
					if (preg_match('/^(\/\/)|(http)/i',$src)) {
						if (strpos($src, '//') === 0) {
							$src = 'https:'.$src;
						}
						$code = file_get_contents($src);

					} else {
						$files[] = c::config()->dirs->www.preg_replace('/^(.*)(\?.*)$/','\\1', $src);
					}

				} else {
					$code = $script->nodeValue;
				}
				
				if ($code) {
					$tmp = tempnam('/tmp',$cacheid);
					$tmps[] = $tmp;
					file_put_contents($tmp,$code);
					$files[] = $tmp;
				}
			}
			
			if ($tmps) {
				foreach ($tmps as $tmp) {
					unlink($tmp);
				}
			}

			Cana::app()->cache()->write($cacheid, $data);
			$data = $this->serve($files, true);

		}

		if ($data['headers']) {
			foreach ($data['headers'] as $key => $header) {
				if ($key == '_responseCode') {
					header($header);
				} else {
					header($key.': '.$header);
				}
			}
		}

		echo $data['content'];
		exit;

	}
}
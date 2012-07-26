<?

class Controller_assets_js_bundle_js extends Crunchbutton_Controller_AssetBundle {
	public function init() {
		$cacheid = 'crunchr-bundle-node-code'.$_REQUEST['v'];
		
		if (Cana::app()->cache()->cached($cacheid)) {
			$data = Cana::app()->cache()->read($cacheid);

		} else {
	
			$src = c::view()->render('bundle/js');
	
			$doc = new DOMDocument('1.0');
			@$doc->loadHTML($src);
	
			foreach ($doc->getElementsByTagName('script') as $script) {
				if ($script->getAttribute('src')) {
					$files[] = '/Users/arzynik/Sites/crunchbutton/include/../www'.preg_replace('/^(.*)(\?.*)$/','\\1',$script->getAttribute('src'));
				} else {
					$code = $script->nodeValue;
					$tmp = tempnam('/tmp',$cacheid);
					$tmps[] = $tmp;
					file_put_contents($tmp,$code);
					$files[] = $tmp;
				}
			}
	
			$data = $this->serve($files);
	
			foreach ($tmps as $tmp) {
				unlink($tmp);
			}

			Cana::app()->cache()->write($cacheid, $data);
		}
		
		foreach ($data['headers'] as $key => $header) {
			header($key.': '.$header);
		}
		
		echo $data['content'];
		exit;

	}
}
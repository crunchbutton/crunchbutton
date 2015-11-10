<?

class Controller_assets extends Crunchbutton_Controller_AssetBundle {
	public function init() {
		$pages = c::pages();
		array_shift($pages);

		$file = array_pop($pages);
		$path = implode('/',$pages);

		if ($file == 'bundle.css') {
			array_shift(c::config()->controllerStack);
			c::displayPage('assets/css/bundle.css');
			exit;
		}

		if ($file == 'bundle.js') {
			array_shift(c::config()->controllerStack);
			c::displayPage('assets/js/bundle.js');
			exit;
		}

		if( preg_match('/\.scss$/i',$_SERVER['REDIRECT_URL']) || (strpos($_SERVER['REQUEST_URI'],'.scss') !== false) ){
			$path = c::config()->dirs->www.'assets/'.$path.'/';
			Crunchbutton_Scss::serve($path.$file);
		}
	}
}

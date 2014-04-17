<?

class Controller_assets extends Crunchbutton_Controller_AssetBundle {
	public function init() {

		$pages = c::pages();
		array_shift($pages);

		$file = array_pop($pages);
		$path = implode('/',$pages);

		if (preg_match('/\.scss$/i',$_SERVER['REDIRECT_URL'])) {
			$path = c::config()->dirs->www.'assets/'.$path.'/';
			$file = $file.'.scss';
			Scss::serve($path.$file);
		}
	}
}

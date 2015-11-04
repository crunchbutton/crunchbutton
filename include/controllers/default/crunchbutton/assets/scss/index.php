<?

class Controller_assets_scss extends Crunchbutton_Controller_AssetBundle {
	public function init() {
		$path = c::config()->dirs->www.'assets/scss/';
		$file = c::getPagePiece(2);
		// test
		if (getenv('DOCKER')) {
			die($file);
		}
		Scss::serve($path.$file);
	}
}

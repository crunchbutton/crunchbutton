<?

class Controller_assets_css_bundle_css extends Crunchbutton_Controller_AssetBundle {
	public function init() {
		$this->serve($this->assets('css'));
	}
}
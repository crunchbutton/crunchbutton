<?

class Controller_assets_scss extends Crunchbutton_Controller_AssetBundle {
	public function init() {
		$this->cacheServe('crunchr-file-scss');
	}
	
	public function getData() {

		$path = $file = c::config()->dirs->www.'assets/scss/';
		$file = c::getPagePiece(2).'.scss';

		$data = Scss::compile($path.$file);
		
		$data = preg_replace('/\t|\n/','',$data);
		$mtime = time();
		
		return ['mtime' => $mtime, 'data' => $data];
	}
}

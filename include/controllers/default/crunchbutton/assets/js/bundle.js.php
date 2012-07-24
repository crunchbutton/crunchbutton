<?

class Controller_assets_js_bundle_js extends Crunchbutton_Controller_AssetBundle {
	public function init() {
//		$files = $this->assets('js');
		$files[] = '/Users/arzynik/Sites/crunchbutton/include/../www/assets/js/jquery.min.js';
		$files[] = '/Users/arzynik/Sites/crunchbutton/include/../www/assets/js/jquery-ui.min.js';
		$files[] = '/Users/arzynik/Sites/crunchbutton/include/../www/assets/js/jquery.cookie.js';
		$files[] = '/Users/arzynik/Sites/crunchbutton/include/../www/assets/js/jquery.history.js';
		$files[] = '/Users/arzynik/Sites/crunchbutton/include/../www/assets/js/underscore-min.js';
		
				
		$files[] = '/Users/arzynik/Sites/crunchbutton/include/../www/assets/js/app.js';
		$files[] = '/Users/arzynik/Sites/crunchbutton/include/../www/assets/js/community.js';
		$files[] = '/Users/arzynik/Sites/crunchbutton/include/../www/assets/js/dish.js';
		$files[] = '/Users/arzynik/Sites/crunchbutton/include/../www/assets/js/extra.js';
		$files[] = '/Users/arzynik/Sites/crunchbutton/include/../www/assets/js/order.js';
		$files[] = '/Users/arzynik/Sites/crunchbutton/include/../www/assets/js/restaurant.js';
		$files[] = '/Users/arzynik/Sites/crunchbutton/include/../www/assets/js/side.js';
		$files[] = '/Users/arzynik/Sites/crunchbutton/include/../www/assets/js/substitution.js';
		$files[] = '/Users/arzynik/Sites/crunchbutton/include/../www/assets/js/topping.js';

		usort($files,'self::jSort');
		$this->serve($files);
	}
	
	public static function jSort($a, $b) {

		$getSort = function($var) {
			if (preg_match('/jquery\.min\.js$/',$var)) {
				$v = 0;
			} elseif (preg_match('/jquery-ui\./',$var)) {
				$v = .1;
			} elseif (preg_match('/underscore/',$var)) {
				$v = .2;
			} elseif (preg_match('/jquery\./',$var)) {
				$v = .8;
			} elseif (preg_match('/app\.js$/',$var)) {
				$v = .9;
			} else {
				$v = 1;
			}
			return $v;
		};

		return strcasecmp($getSort($a), $getSort($b));
	}
	

}
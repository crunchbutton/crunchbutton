<?

class Controller_assetstest extends Crunchbutton_Controller_AssetBundle {
	public function init() {
		$pages = c::pages();

		echo "==============================================================\n";
		echo "==============================================================\n";
		echo "==============================================================\n";
		echo "==============================================================\n";
		echo "\n\n\nPAGES::\n";
		echo '<pre>';var_dump( $pages );

		array_shift($pages);

		$file = array_pop($pages);
		$path = implode('/',$pages);

		echo "==============================================================\n";
		echo "==============================================================\n";
		echo "==============================================================\n";
		echo "==============================================================\n";
		echo "\n\n\nFILE::\n";
		echo '<pre>';var_dump( $file );

		echo "==============================================================\n";
		echo "==============================================================\n";
		echo "==============================================================\n";
		echo "==============================================================\n";
		echo "\n\n\nPATH::\n";
		echo '<pre>';var_dump( $path );

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

		if (preg_match('/\.scss$/i',$_SERVER['REDIRECT_URL'])) {
			$path = c::config()->dirs->www.'assets/'.$path.'/';
			Scss::serve($path.$file);
		}

		echo "==============================================================\n";
		echo "==============================================================\n";
		echo "==============================================================\n";
		echo "==============================================================\n";
		echo "\n\n\nPATH::\n";
		echo '<pre>';var_dump( $path );


	}
}

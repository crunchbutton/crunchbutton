<?

class Crunchbutton_Scss extends Cana_Model {
	public static function compile($file) {

		if (!file_exists($file)) {
			return false;
		}

		$path = dirname($file).'/';
		$file = basename($file);

		$scss = new Scss_Scss;
		$scss->setImportPaths($path);
		$scss->setFormatter('scss_formatter_compressed');
		$data = $scss->compile(file_get_contents($path.$file));
		
		return $data;
	}
}
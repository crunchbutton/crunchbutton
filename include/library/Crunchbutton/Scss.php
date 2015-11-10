<?

class Crunchbutton_Scss extends Cana_Model {
	public static function compile($file) {

		echo "==============================================================\n";
		echo "==============================================================\n";
		echo "==============================================================\n";
		echo "==============================================================\n";
		echo "\n\n\nfile_exists::\n";
		echo '<pre>' . file_exists($file);
		echo "\n";

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

	public static function serve($file) {

		$data = self::compile($file);
		$mtime = filemtime($file);

		header('HTTP/1.1 200 OK');
		header('Date: '.date('r'));
		header('Last-Modified: '.gmdate('D, d M Y H:i:s',$mtime).' GMT');
		header('Accept-Ranges: bytes');
		header('Content-Length: '.strlen($data));
		header('Content-type: text/css');
		header('Vary: Accept-Encoding');
		header('Cache-Control: max-age=290304000, public');

		echo $data;
		exit;
	}
}
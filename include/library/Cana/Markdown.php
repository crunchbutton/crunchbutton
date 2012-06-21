<?php

/**
 * A markdown parser wrapper
 *
 * @author		Devin Smith <devin@cana.la>
 * @date		2010.06.03
 *
 */


// auto include the markdown parser
$md = new Cana_Markdown_Markdown;

class Cana_Markdown extends Cana_Model {

	public static function parse($text) {
		$text = Markdown($text);

		return $text;
	}

}
<?php

class Cana_View_Helper_ImageBase64 {
	public function output($image) {
		$img = new Cana_ImageBase64($image);
		return $img->output();
	}
}
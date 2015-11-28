<?php

class Cockpit_Option extends Crunchbutton_Option {
	public function exports() {
		$out = $this->properties();
		$out['price'] = floatval( $out['price'] );
		$out['prices'] = [];
		foreach ($this->prices() as $price) {
			$out['prices'][$price->id_option_price] = $price->exports();
		}
		return $out;
	}
}
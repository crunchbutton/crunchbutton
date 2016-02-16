<?php

class Crunchbutton_Quote_Restaurant extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('quote_restaurant')
			->idVar('id_quote_restaurant')
			->load($id);
	}
	function removeByQuote( $id_quote ){
		c::dbWrite()->query( 'DELETE FROM quote_restaurant WHERE id_quote = "' . $id_quote . '"' );
	}

}


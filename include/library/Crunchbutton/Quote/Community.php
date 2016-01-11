<?php

class Crunchbutton_Quote_Community extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('quote_community')
			->idVar('id_quote_community')
			->load($id);
	}
	function removeByQuote( $id_quote ){
		c::dbWrite()->query( 'DELETE FROM quote_community WHERE id_quote = "' . $id_quote . '"' );
	}

}


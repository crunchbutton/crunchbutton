<?php

class Crunchbutton_Pexcard_Token extends Crunchbutton_Pexcard_Resource {

	public function getToken(){
		$token = Crunchbutton_Pexcard_Token::q( 'SELECT * FROM pexcard_token WHERE env = "' . Crunchbutton_Pexcard_Resource::env() . '" AND active = true ORDER BY date DESC LIMIT 1' );
		if( !$token->count() ){
			return Crunchbutton_Pexcard_Token::createToken();
		}
		else {
			return $token->token;
		}
	}

	public function desactiveToken(){
		$token = Crunchbutton_Pexcard_Token::q( 'SELECT * FROM pexcard_token WHERE env = "' . Crunchbutton_Pexcard_Resource::env() . '" AND active = true ORDER BY date DESC LIMIT 1' );
		if( $token->id_pexcard_token ){
			$token->active = 0;
			$token->save();
		}
	}

	public function createToken(){

		// desactive old tokens
		self::desactiveToken();

		$request = Crunchbutton_Pexcard_Resource::request( 'token', [ 'Username' => Crunchbutton_Pexcard_Resource::username(), 'Password' => Crunchbutton_Pexcard_Resource::password() ] );
		if( $request->body && $request->body->Token ){
			$token = new Crunchbutton_Pexcard_Token;
			$token->token = $request->body->Token;
			$token->date = date( 'Y-m-d H:i:s' );
			$token->env = Crunchbutton_Pexcard_Resource::env();
			$token->active = 1;
			$token->save();
			return $token->token;
		}
		$message = 'Error creating a pex card token' . "\n";
		$message .= 'It is important, please contact Daniel or Devin';
		Crunchbutton_Support::createNewWarning(  [ 'body' => $message ] );
	}

	public function __construct($id = null) {
		parent::__construct();
		$this->table( 'pexcard_token' )->idVar( 'id_pexcard_token' )->load( $id );
	}

}

?>

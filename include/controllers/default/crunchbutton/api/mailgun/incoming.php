<?php

class Controller_Api_Mailgun_Incoming extends Crunchbutton_Controller_Rest {

	public function init() {

		Log::debug( $_POST );

		if( !$_POST ){
			return;
		}

		$params = [];
		$email = self::email( $_POST[ 'from' ] );

		if( $email == Crunchbutton_Support::SUPPORT_EMAIL ){
			return;
		}

		$params[ 'email' ] = $email;
		$params[ 'name' ] = self::name( $_POST[ 'from' ] );
		$params[ 'subject' ] = self::subject( $_POST[ 'subject' ] );
		$params[ 'body' ] = strip_tags( $_POST[ 'body-plain' ] );

		Support::addEmailTicket( $params );
	}

	public static function name( $from ){
		preg_match( '/(<[a-z@\.]+>)/', $from, $results );
		if( $results && $results[ 0 ] ){
			return trim( str_replace( $results[0], '', $from ) );
		}
		return $from;
	}

	public static function email( $from ){
		preg_match( '/[A-Za-z0-9_-]+@[A-Za-z0-9_-]+\.([A-Za-z0-9_-][A-Za-z0-9_]+)/', $from, $results );
		if( $results && $results[ 0 ] ){
			return $results[ 0 ];
		}
		return false;
	}


	public static function subject( $subject ){
		return preg_replace('/([\[\(] *)?(RE|FWD?) *([-:;)\]][ :;\])-]*|$)|\]+ *$/i', '', $subject);
	}

}
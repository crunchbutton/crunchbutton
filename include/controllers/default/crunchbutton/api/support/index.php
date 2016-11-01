<?php

class Controller_api_Support extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ( $this->method() ) {
			// Saves a suggestion
			case 'post':

				switch ( c::getPagePiece(2) ) {
					case 'sms':
						$request = $this->request();
						$from = Phone::clean($request['phone']);
						$body = $request['message'];
						$name = $request['name'];

						$params = [
							'name' => $name,
							'body' => $body,
							'from' => $from];
						$ret = (new Message_Incoming_Customer($params))->response;
						echo json_encode(['success' =>  true]);exit();
					break;
					case 'run-business':
						$this->runBusiness();
						break;
					case 'say':
						$this->say();
						break;
				}

			break;

			case 'get':

				switch ( c::getPagePiece( 2 ) ) {
					case 'say':
						$this->say();
						break;
					default:
						echo json_encode(['error' => 'invalid object']);
						break;
				}
		}
	}

	private function runBusiness(){
		$name = $this->request()['name'];
		$email = $this->request()['email'];
		$phone = $this->request()['phone'];
		$address = $this->request()['address'];
		$reason = $this->request()['reason'];

		$html = 'Name: ' . $name . '<br>';
		$html .= 'Email: ' . $email . '<br>';
		$html .= 'Phone: ' . $phone . '<br>';
		$html .= 'Address: ' . $address . '<br>';
		$html .= 'Why: ' . $reason;

		$res = c::mailgun()->sendMessage(c::config()->mailgun->domain, [
		'from' => 'iwanttobuildmyownbusiness@_DOMAIN_',
		'to' => 'iwanttobuildmyownbusiness@_DOMAIN_',
		'subject'	=> 'I want to build my own business',
		'html' => $html


		]);

	}

	function say(){

		$id_support = c::getPagePiece( 3 );

		Log::debug( [ 'action' => 'Calling', 'id_support' => $id_support, 'type' => 'support' ] );

		$support = Crunchbutton_Support::o( $id_support );

		$message = ' . You received a new support sms . ';

		if( $support->id_support ){

			if( is_numeric( $support->name ) ){
				$name = implode( ' . ', str_split( $support->name ) );
			} else {
				$name = $support->name;
			}

			$phone = implode( ' . ', str_split( $support->phone ) );

			$message .= ' . Name . ' . $name . ' . ';
			$message .= ' . Phone . ' . $phone . ' . ';
			$message .= ' . Message . ' . $support->message . ' . ';
		}

		$supportName = c::getPagePiece( 4 );

		$message .= ' . ';

		Log::debug( [ 'action' => 'Calling', 'said' => $message, 'supportName' => $supportName, 'type' => 'support' ] );

		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
					.'<Response>' . "\n"
						.'<Say voice="' . c::config()->twilio->voice . '">' . "\n"
							. 'Hello ' . $supportName . ' . , ' . "\n"
							. $message . "\n"
						.'</Say>' . "\n"
					.'</Response>';
	}

}
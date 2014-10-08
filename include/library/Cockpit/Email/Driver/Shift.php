<?php


class Cockpit_Email_Driver_Shift extends Crunchbutton_Email {
	private $_mailConfig;

	public function __construct( $params ) {

		$email = $params['email'];
		$message = $params['message'];

		if( $email ){

			$params['to'] 				= $email . ', Crunchbutton Copy <_USERNAME_>';
			$params['subject'] 		= "You're scheduled for the following shifts";
			$params['from'] 			= 'Tech Support <tech@_DOMAIN_>';
			$params['reply']			= 'Tech Support <tech@_DOMAIN_>';

			$this->buildView( $params );

			$this->view()->subject = $params[ 'subject' ];
			$this->view()->email	 = $params[ 'email' ];
			$this->view()->message = $params[ 'message' ];

			$params['messageHtml']		= $this->view()->render('driver/notification/shift',['display' => true, 'set' => [
				'email'  => $admin->email,
				'message' => $message
			]]);

			parent::__construct($params);
		}
	}
}

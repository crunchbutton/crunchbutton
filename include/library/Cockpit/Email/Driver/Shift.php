<?php


class Cockpit_Email_Driver_Shift extends Crunchbutton_Email {
	private $_mailConfig;

	public function __construct( $params ) {

		$email = $params['email'];
		$message = $params['message'];

		if( $email ){

			$params['to'] 				= $email;
			$params['subject'] 		= "You're scheduled for the following shifts";
			$params['from'] 			= 'Crunchbutton <support@crunchbutton.com>';
			$params['reply']			= 'Crunchbutton <support@crunchbutton.com>';

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

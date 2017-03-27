<?php


class Cockpit_Email_Driver_Broadcast extends Crunchbutton_Email {

	private $_mailConfig;

	public function __construct( $params ) {

		$driver = $params[ 'driver' ];

		if( $driver->email ){

			$params['to'] 				= $driver->email;
			$params['subject'] 		= $params['subject'];
			$params['from'] 			= 'Crunchbutton <payment@crunchbutton.com>';
			$params['reply']			= 'Crunchbutton <payment@crunchbutton.com>';

			$this->buildView( $params );

			$this->view()->subject = $params['subject'];
			$this->view()->email	 = $params['email'];
			$params['messageHtml'] = $params['message'];
			parent::__construct($params);
		}
	}
}

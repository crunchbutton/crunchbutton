<?php


class Cockpit_Email_Driver_Welcome extends Crunchbutton_Email {
	private $_mailConfig;
	
	public function __construct( $params ) {

		$id_admin = $params['id_admin'];
		$admin = Crunchbutton_Admin::o( $id_admin );

		if( $admin->email ){

			$params['to'] 				= $admin->email.', Crunchbutton Copy <_USERNAME_>';
			$params['subject'] 		= 'Welcome to Crunchbutton';
			$params['from'] 			= 'Tech Support <tech@_DOMAIN_>';
			$params['reply']			= 'Tech Support <tech@_DOMAIN_>';

			$this->buildView( $params );

			$this->view()->subject = $params['subject'];
			$this->view()->email	 = $params['email'];
			$this->view()->name	 	 = $admin->name;
			$this->view()->phone	 = $admin->phone;

			$params['messageHtml']		= $this->view()->render('driver/notification/welcome',['display' => true, 'set' => [
				'name'  => $admin->name,
				'phone' => $admin->phone
			]]);

			parent::__construct($params);				
		}
	}
}

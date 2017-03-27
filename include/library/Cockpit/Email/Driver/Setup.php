<?php


class Cockpit_Email_Driver_Setup extends Crunchbutton_Email {
	private $_mailConfig;
	
	public function __construct( $params ) {

		$id_admin = $params['id_admin'];
		$admin = Crunchbutton_Admin::o( $id_admin );

		if( $admin->email ){

			$params['to'] 				= $admin->email;
			$params['subject'] 		= 'Access info';
			$params['from'] 			= 'Crunchbutton <support@crunchbutton.com>';
			$params['reply']			= 'Crunchbutton <support@crunchbutton.com>';

			$this->buildView( $params );

			$this->view()->subject = $params['subject'];
			$this->view()->email	 = $params['email'];
			$this->view()->name	 	 = $admin->name;
			$this->view()->login	 = $admin->login;

			$params['messageHtml']		= $this->view()->render('driver/notification/setup',['display' => true, 'set' => [
				'name'  => $admin->name,
				'login' => $admin->login
			]]);

			parent::__construct($params);				
		}
	}
}

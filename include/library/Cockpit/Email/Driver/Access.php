<?php


class Cockpit_Email_Driver_Access extends Crunchbutton_Email {
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
			$this->view()->login	 	 = $admin->login;
			$this->view()->pass 	 = $params['pass'];

			$params['messageHtml']		= $this->view()->render('driver/notification/access',['display' => true, 'set' => [
				'name' => $admin->name,
				'login' => $admin->login,
				'pass' => $params['pass']
			]]);

			parent::__construct($params);
		}
	}
}

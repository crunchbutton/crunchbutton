<?php

class Crunchbutton_Email_Payment_Error extends Crunchbutton_Email {

	private $_mailConfig;

	public function __construct( $params ) {

		$params['to'] = 'payment@crunchbutton.com';
		$params['from'] = 'Crunchbutton<support@crunchbutton.com>';
		$params['reply'] = 'Crunchbutton<no-reply@crunchbutton.com>';

		$this->buildView( $params );
		$this->view()->subject = $params['subject'] ;
		$this->view()->email = $params['to'];
		$body = str_replace("\n", "\n<br>", $params['body']);

		$params['messageHtml'] = $body;
		parent::__construct($params);
	}
}

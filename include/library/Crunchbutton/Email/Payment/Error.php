<?php

class Crunchbutton_Email_Payment_Error extends Crunchbutton_Email {

	private $_mailConfig;

	public function __construct( $params ) {

		$params['to'] = 'payments@_DOMAIN_';
		$params['from'] = 'Crunchbutton<support@_DOMAIN_>';
		$params['reply'] = 'Crunchbutton<no-reply@_DOMAIN_>';

		$this->buildView( $params );
		$this->view()->subject = $params['subject'] ;
		$this->view()->email = $params['to'];
		$body = str_replace("\n", "\n<br>", $params['body']);

		$params['messageHtml'] = $body;
		parent::__construct($params);
	}
}

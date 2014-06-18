<?php

class Crunchbutton_Email_Payment_Summary extends Crunchbutton_Email {

	private $_mailConfig;

	public function __construct( $params ) {

		$params['to'] = $params[ 'summary' ]['summary_email'].', Crunchbutton Copy <_USERNAME_>';
		$params['subject'] = $params[ 'summary' ][ 'notes' ];
		$params['from'] = 'Crunchbutton <tech@_DOMAIN_>';
		$params['reply'] = 'Tech Support <tech@_DOMAIN_>';
		$params['reply'] = 'Tech Support <tech@_DOMAIN_>';

		$this->buildView( $params );
		$this->view()->subject = $params[ 'summary' ]['notes'];
		$this->view()->email = $params[ 'summary' ]['summary_email'];

		$params['messageHtml'] = $this->view()->render( 'payment/summary',[ 'display' => true, 'set' => [ 'summary' => $params['summary'] ] ] );

		parent::__construct($params);
	}
}

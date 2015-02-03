<?php

class Crunchbutton_Email_Payment_Summary extends Crunchbutton_Email {

	private $_mailConfig;

	public function __construct( $params ) {

		$params['to'] = $params[ 'summary' ]['summary_email'];
		$params['subject'] = $params[ 'summary' ][ 'notes' ];
		$params['from'] = 'Crunchbutton <support@_DOMAIN_>';
		$params['reply'] = 'Crunchbutton <support@_DOMAIN_>';
		$params['reply'] = 'Crunchbutton <support@_DOMAIN_>';

		$this->buildView( $params );
		$this->view()->subject = $params[ 'summary' ]['notes'];
		$this->view()->email = $params[ 'summary' ]['summary_email'];

		if( $params[ 'summary' ][ 'type' ] == Cockpit_Payment_Schedule::TYPE_DRIVER ){
			$params['messageHtml'] = $this->view()->render( 'payment/summary-driver',[ 'display' => true, 'set' => [ 'summary' => $params['summary'] ] ] );
		} else {
			$params['messageHtml'] = $this->view()->render( 'payment/summary-restaurant',[ 'display' => true, 'set' => [ 'summary' => $params['summary'] ] ] );
		}
		parent::__construct($params);
	}
}

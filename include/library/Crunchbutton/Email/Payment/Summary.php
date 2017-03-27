<?php

class Crunchbutton_Email_Payment_Summary extends Crunchbutton_Email {

	private $_mailConfig;

	public function __construct( $params ) {

		$params['to'] = $params[ 'summary' ]['summary_email'];
		$params['subject'] = $params[ 'summary' ][ 'note' ];
		$params['from'] = 'Crunchbutton <support@crunchbutton.com>';
		$params['reply'] = 'Crunchbutton <support@crunchbutton.com>';
		$params['reply'] = 'Crunchbutton <support@crunchbutton.com>';

		$this->buildView( $params );
		$this->view()->subject = $params['subject'] ;
		$this->view()->email = $params[ 'summary' ]['summary_email'];
		if( $params[ 'summary' ][ 'type' ] == Cockpit_Payment_Schedule::TYPE_DRIVER ){
			if( $params[ 'summary' ][ 'arbritary' ] ){
				$params['messageHtml'] = $this->view()->render( 'payment/summary-driver-arbritary',[ 'display' => true, 'set' => [ 'summary' => $params['summary'] ] ] );
			} else {
				$params['messageHtml'] = $this->view()->render( 'payment/summary-driver',[ 'display' => true, 'set' => [ 'summary' => $params['summary'] ] ] );
			}
		} else {
			if( $params[ 'summary' ][ 'arbritary' ] ){
				$params['messageHtml'] = $this->view()->render( 'payment/summary-restaurant-arbritary',[ 'display' => true, 'set' => [ 'summary' => $params['summary'] ] ] );
			} else {
				$params['messageHtml'] = $this->view()->render( 'payment/summary-restaurant',[ 'display' => true, 'set' => [ 'summary' => $params['summary'] ] ] );
			}
		}
		parent::__construct($params);
	}
}

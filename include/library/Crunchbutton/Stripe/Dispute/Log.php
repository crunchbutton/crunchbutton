<?php

class Crunchbutton_Stripe_Dispute_Log extends Cana_Table {

	public function create( $params ){
		$log = new Crunchbutton_Stripe_Dispute_Log;
		$log->id_stripe_dispute = $params[ 'id_stripe_dispute' ];
		if( $params[ 'id_stripe_webhook' ] ){
			$log->id_stripe_webhook = $params[ 'id_stripe_webhook' ];
		} else if( $params[ 'id_stripe_dispute_evidence' ] ){
			$log->id_stripe_dispute_evidence = $params[ 'id_stripe_dispute_evidence' ];
		}
		$log->datetime = date( 'Y-m-d H:i:s' );
		$log->save();

		if( $log->id_stripe_webhook ){
			$log->updateDisputeStatus();
		}
	}

	public function exports(){
		$out = $this->properties();
		if( $this->webhook() ){
			$webhook = $this->webhook();
			$out[ 'data' ] = $webhook->data();
			$out[ 'date' ] = $webhook->date()->format( 'c' );
		} else if( $this->evidence() ){
			$evidence = $this->evidence();
			$out[ 'data' ] = $evidence->exportsEvidence();
			$out[ 'date' ] = $evidence->date()->format( 'c' );

		}
		$out[ 'type' ] = $this->type();
		return $out;
	}

	public function updateDisputeStatus(){
		$this->dispute()->setStatus( $this->status() );
	}

	public function dispute(){
		if( !$this->dispute ){
			$this->dispute = Crunchbutton_Stripe_Dispute::o( $this->id_stripe_dispute );
		}
		return $this->dispute;
	}

	public function webhook(){
		if( $this->id_stripe_webhook && !$this->_webook ){
			$this->_webook = Crunchbutton_Stripe_Webhook::o( $this->id_stripe_webhook );
		}
		return $this->_webook;
	}

	public function webhookData(){
		if( !$this->_webook_data ){
			if( $this->webhook() ){
				$this->_webook_data = $this->webhook()->data();
			}
		}

		return $this->_webook_data;
	}

	public function status(){
		if( $this->webhook() ){
			$this->_status = $this->webhookData()->data->object->status;
		} else if( $this->evidence() ){
			$this->_status = $this->evidence()->status;
		}
		return $this->_status;
	}

	public function evidence(){
		if( $this->id_stripe_dispute_evidence && !$this->_evidence ){
			$this->_evidence = Crunchbutton_Stripe_Dispute_Evidence::o( $this->id_stripe_dispute_evidence );
		}
		return $this->_evidence;
	}

	public function submissionCount(){
		if( !$this->_submission_count ){
			$this->_submission_count = $this->webhookData()->data->object->evidence_details->submission_count;
		}
		return $this->_submission_count;
	}

	public function webhookType(){
		return $this->webhook()->webhookType()->type;
	}

	public function type(){
		if( $this->webhook() ){
			return $this->webhookType();
		} else if( $this->evidence() ){
			return 'evidence';
		}
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime( $this->date, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_date;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('stripe_dispute_log')
			->idVar('id_stripe_dispute_log')
			->load($id);
	}
}

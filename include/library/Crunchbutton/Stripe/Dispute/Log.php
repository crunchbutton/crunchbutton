<?php

class Crunchbutton_Stripe_Dispute_Log extends Cana_Table {

	public function create( $params ){
		$log = new Crunchbutton_Stripe_Dispute_Log;
		$log->id_stripe_dispute = $params[ 'id_stripe_dispute' ];
		$log->id_stripe_webhook = $params[ 'id_stripe_webhook' ];
		if( $params[ 'id_admin' ] ){
			$log->id_admin = $params[ 'id_admin' ];
		}
		$log->datetime = date( 'Y-m-d H:i:s' );
		$log->save();

		$log->updateDisputeStatus();
	}

	public function exports(){
		$out = $this->properties();
		$out[ 'data' ] = $this->webhookData();
		return $out;
	}

	public function updateDisputeStatus(){
		$this->dispute()->setStatus( $this->status() );
	}

	public function updateSubmissionCount(){
		$this->dispute()->setSubmissionCount( $this->submissionCount() );
	}

	public function dispute(){
		if( !$this->dispute ){
			$this->dispute = Crunchbutton_Stripe_Dispute::o( $this->id_stripe_dispute );
		}
		return $this->dispute;
	}

	public function webhook(){
		if( !$this->_webook ){
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
		if( !$this->_status ){
			$this->_status = $this->webhookData()->data->object->status;
		}
		return $this->_status;
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
		return $this->webhookType();
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

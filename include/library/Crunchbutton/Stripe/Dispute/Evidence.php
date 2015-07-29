<?php

class Crunchbutton_Stripe_Dispute_Evidence extends Cana_Table {

	const STATUS_SENT = 'sent';
	const STATUS_DRAFT = 'draft';

	public function create( $params ){
		$evidence = new Crunchbutton_Stripe_Dispute_Evidence;
		if( $params[ 'id_admin' ] ){
			$evidence->id_admin = $params[ 'id_admin' ];
		}
		$evidence->id_stripe_dispute = $params[ 'id_stripe_dispute' ];
		$evidence->datetime = date( 'Y-m-d H:i:s' );
		$evidence->status = self::STATUS_DRAFT;
		$evidence->save();
		return $evidence;
	}

	public function exports(){
		$out = $this->properties();

		if( $this->status == self::STATUS_SENT ){
			$log = $this->log();
			if( $log ){
				$out[ 'sent_date' ] = $log->date()->format( 'c' );
			}
		}

		if( $this->admin() ){
			$out[ 'send_by' ] = $this->admin()->name;
		}

		return $out;
	}

	public function admin(){
		if( !$this->_admin && $this->id_admin ){
			$this->_admin = Admin::o( $this->id_admin );
		}
		return $this->_admin;
	}

	public function log(){
		if( !$this->_log ){
			$log = Crunchbutton_Stripe_Dispute_Log::q( 'SELECT * FROM stripe_dispute_log WHERE id_stripe_dispute_evidence = ? AND id_stripe_dispute = ? ORDER BY id_stripe_dispute_log DESC LIMIT 1', [ $this->id_stripe_dispute_evidence, $this->id_stripe_dispute ] )->get( 0 );
			if( $log->id_stripe_dispute_log ){
				$this->_log = $log;
			}
		}
		return $this->_log;
	}

	public function generateEvidence(){

		$dispute = $this->dispute();

		$separator = ";\n";

		$order = $dispute->order();
		$restaurant = $order->restaurant();
		$user = $order->user();
		$evidence = [];
		$this->product_description = 'Order #' . $order->id_order . ' from restaurant ' . $restaurant->name . ': ' . $order->orderMessage( 'support' );
		$this->service_date = $order->date()->format( 'Y-m-d H:i:s' );
		$this->customer_name = $user->name;

		$status = $order->status()->last();
		if( $status[ 'status' ] == 'delivered' ){
			$date = new DateTime( $status[ 'date' ], new DateTimeZone( c::config()->timezone ) );
			$this->shipping_date = $date->format( 'Y-m-d H:i:s' );
		}

		if( $user->email ){
			$this->customer_email_address = $user->email;
		}
		$this->shipping_address = $order->address;
		$this->customer_purchase_ip = $order->ip();

		$this->uncategorized_text = 'Customer phone number: ' . $order->phone();

		if( $status[ 'status' ] == 'delivered' && $status[ 'driver' ][ 'name' ] ){
			$this->uncategorized_text .= $separator . 'Driver: ' . $status[ 'driver' ][ 'name' ];
		}

		$agent = $order->agent();
		$this->uncategorized_text .= $separator . 'User Agent: Browser: ' . $agent->browser . ' version(' . $agent->version . ')';
		$this->uncategorized_text .= ', OS: ' . $agent->os;

		if( $order->notes ){
			$this->uncategorized_text .= $separator . 'Additional notes on the order: ' . $order->notes;
		}
		$this->save();
	}

	public function exportsEvidence(){
		$out = [];
		$out[ 'product_description'] = $this->product_description;
		$out[ 'customer_name'] = $this->customer_name;
		$out[ 'customer_email_address'] = $this->customer_email_address;
		$out[ 'customer_purchase_ip'] = $this->customer_purchase_ip;
		$out[ 'customer_signature'] = $this->customer_signature;
		$out[ 'billing_address'] = $this->billing_address;
		$out[ 'receipt'] = $this->receipt;
		$out[ 'shipping_address'] = $this->shipping_address;
		$out[ 'shipping_date'] = $this->shipping_date;
		$out[ 'shipping_carrier'] = $this->shipping_carrier;
		$out[ 'shipping_tracking_number'] = $this->shipping_tracking_number;
		$out[ 'shipping_documentation'] = $this->shipping_documentation;
		$out[ 'access_activity_log'] = $this->access_activity_log;
		$out[ 'service_date'] = $this->service_date;
		$out[ 'service_documentation'] = $this->service_documentation;
		$out[ 'duplicate_charge_id'] = $this->duplicate_charge_id;
		$out[ 'duplicate_charge_explanation'] = $this->duplicate_charge_explanation;
		$out[ 'duplicate_charge_documentation'] = $this->duplicate_charge_documentation;
		$out[ 'refund_policy'] = $this->refund_policy;
		$out[ 'refund_policy_disclosure'] = $this->refund_policy_disclosure;
		$out[ 'refund_refusal_explanation'] = $this->refund_refusal_explanation;
		$out[ 'cancellation_policy'] = $this->cancellation_policy;
		$out[ 'cancellation_policy_disclosure'] = $this->cancellation_policy_disclosure;
		$out[ 'cancellation_rebuttal'] = $this->cancellation_rebuttal;
		$out[ 'customer_communication'] = $this->customer_communication;
		$out[ 'uncategorized_text'] = $this->uncategorized_text;
		$out[ 'uncategorized_file'] = $this->uncategorized_file;
		return $out;
	}

	public function dispute(){
		if( !$this->dispute ){
			$this->dispute = Crunchbutton_Stripe_Dispute::o( $this->id_stripe_dispute );
		}
		return $this->dispute;
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
			->table('stripe_dispute_evidence')
			->idVar('id_stripe_dispute_evidence')
			->load($id);
	}
}

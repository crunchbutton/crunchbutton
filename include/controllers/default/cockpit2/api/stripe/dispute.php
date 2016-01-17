<?php

class Controller_Api_Stripe_Dispute extends Crunchbutton_Controller_Rest {

	public function init() {

		$this->_permissionDenied();

		 switch ( c::getPagePiece( 3 ) ) {
		 	case 'evidence':
		 		$evidence = $this->_evidence();
				echo json_encode( $evidence->exports() );exit();
		 		break;
			case 'upload':
				$this->_upload();
				break;

		 	default:
				// dispute api stuff
				$dispute = $this->_dispute();
				switch ( c::getPagePiece( 4 ) ) {

					case 'last-evidence':
						if ( $this->method() == 'post' ) {

							$evidence = new Crunchbutton_Stripe_Dispute_Evidence;
							$last_evidence = $dispute->lastEvidence();

							$evidence->customer_purchase_ip = $last_evidence->customer_purchase_ip;
							$evidence->shipping_date = $last_evidence->shipping_date;
							$evidence->service_date = $last_evidence->service_date;
							$evidence->service_documentation = $last_evidence->service_documentation;
							$evidence->duplicate_charge_id = $last_evidence->duplicate_charge_id;
							$evidence->duplicate_charge_explanation = $last_evidence->duplicate_charge_explanation;
							$evidence->duplicate_charge_documentation = $last_evidence->duplicate_charge_documentation;
							$evidence->refund_policy = $last_evidence->refund_policy;
							$evidence->refund_policy_disclosure = $last_evidence->refund_policy_disclosure;
							$evidence->refund_refusal_explanation = $last_evidence->refund_refusal_explanation;
							$evidence->cancellation_policy = $last_evidence->cancellation_policy;
							$evidence->cancellation_policy_disclosure = $last_evidence->cancellation_policy_disclosure;
							$evidence->cancellation_rebuttal = $last_evidence->cancellation_rebuttal;
							$evidence->cancellation_rebuttal = $last_evidence->cancellation_rebuttal;
							$evidence->uncategorized_file = $last_evidence->uncategorized_file;
							$evidence->customer_communication = $last_evidence->customer_communication;


							if( $this->request()[ 'receipt_url' ] ){
								$evidence->receipt_url = $this->request()[ 'receipt_url' ];
							} else {
								$evidence->receipt_url = $last_evidence->receipt_url;
							}

							$evidence->id_stripe_dispute = $this->request()[ 'id_stripe_dispute' ];
							$evidence->id_admin = c::user()->id_admin;
							$evidence->datetime = date( 'Y-m-d H:i:s' );
							$evidence->status = Crunchbutton_Stripe_Dispute_Evidence::STATUS_DRAFT;
							$evidence->product_description = $this->request()[ 'product_description' ];
							$evidence->customer_name = $this->request()[ 'customer_name' ];
							$evidence->customer_email_address = $this->request()[ 'customer_email_address' ];
							$evidence->customer_signature = $this->request()[ 'customer_signature' ];
							$evidence->billing_address = $this->request()[ 'billing_address' ];
							$evidence->shipping_address = $this->request()[ 'shipping_address' ];
							$evidence->shipping_carrier = $this->request()[ 'shipping_carrier' ];
							$evidence->shipping_tracking_number = $this->request()[ 'shipping_tracking_number' ];
							$evidence->shipping_documentation = $this->request()[ 'shipping_documentation' ];
							$evidence->access_activity_log = $this->request()[ 'access_activity_log' ];
							$evidence->uncategorized_text = $this->request()[ 'uncategorized_text' ];
							$evidence->receipt = $this->request()[ 'receipt' ];
							$evidence->save();
							if( $this->request()[ 'send' ] ){
								$dispute->updateDispute();
								echo json_encode( [ 'success' => 'sent' ] );exit();
							} else {
								echo json_encode( [ 'success' => 'saved' ] );exit();
							}
						} else {
							$evidence = $dispute->lastEvidence();
							echo json_encode( $evidence->exports() );exit();
						}
						break;

					default:
						$this->_export( $dispute );
						break;
				}
		 		break;
		 }

	}

	private function _evidence(){
		$evidence = Crunchbutton_Stripe_Dispute_Evidence::o( c::getPagePiece( 4 ) );
		if( !$evidence->id_stripe_dispute_evidence ){
			$this->error(404, true);
		}
		return $evidence;
	}

	private function _dispute(){
		$dispute = Crunchbutton_Stripe_Dispute::o( c::getPagePiece( 3 ) );
		if( !$dispute->id_stripe_dispute ){
			$this->error(404, true);
		}
		return $dispute;
	}

	private function _upload(){
		if( $_FILES ){
			$ext = pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION );
			if( Util::allowedExtensionUpload( $ext ) ){
				$name = $_REQUEST['filename'] ? $_REQUEST['filename'] : pathinfo( $_FILES['file']['name'], PATHINFO_FILENAME );
				$name = str_replace( $ext, '', $name );
				$random = substr( str_replace( '.' , '', uniqid( rand(), true ) ), 0, 8 );
				$name = Util::slugify( $random . '-' . $name );
				$name = substr( $name, 0, 40 ) . '.'. $ext;
				$file = $_FILES['file']['tmp_name'];
				$file = Crunchbutton_Stripe_Dispute::uploadReceipt( $file );
				echo json_encode( [ 'success' => $file ] );exit();
			} else {
				echo json_encode( [ 'error' => 'invalid extension' ] );exit();
			}
		} else {
			$this->error(404, true);
		}
	}

	private function _export( $dispute ){
		$out = $dispute->properties();
		$out[ 'date' ] = $dispute->date()->format( 'c' );
		$out[ 'charge_id' ] = $dispute->charge_id();
		$out[ 'charged' ] = $dispute->order()->charged();
		$out[ 'reason' ] = ucfirst( $out[ 'reason' ] );
		$out[ 'order' ] = $dispute->order()->exports();
		$out[ 'user' ] = $dispute->order()->user()->id_user ? $dispute->order()->user()->exports() : null;
		$out[ 'can_send_more' ] = $dispute->canSendMoreEvidences();
		$out[ 'log' ] = [];
		$log_data = $dispute->log();
		foreach( $log_data as $log ){
			$out[ 'log' ][] = $log->exports();
		}
		echo json_encode( $out );exit();
	}

	private function _permissionDenied(){
		if (!c::admin()->permission()->check( [ 'global', 'disputes-all' ] ) ) {
			$this->error(401, true);
		}
	}
}

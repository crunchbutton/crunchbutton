<?php

class Controller_api_temp_sandbox extends Crunchbutton_Controller_RestAccount {

	public function init() {



		$dispute = Crunchbutton_Stripe_Dispute::o( 8 );
		$dispute->uploadReceipt( './test.png' );
		exit;

		// $e = new Crunchbutton_Stripe_Dispute_Evidence;
		// $e->id_stripe_dispute = 28;
		// $e->generateEvidence();
		// echo '<pre>';var_dump( $e->exportsEvidence() );exit();


		// $data = json_decode( '{"id":"evt_16TZxJJMXBWnTQ4rf697TNLU","created":1438097125,"livemode":false,"type":"charge.dispute.updated","data":{"object":{"id":"dp_16TZHNJMXBWnTQ4r9SuiDne7","charge":"ch_16TZHIJMXBWnTQ4rmSoWK3kR","amount":3890,"created":1438094525,"status":"under_review","livemode":false,"currency":"usd","object":"dispute","reason":"general","is_charge_refundable":false,"balance_transactions":[{"id":"txn_16TZHNJMXBWnTQ4rFLuHNozv","object":"balance_transaction","amount":-3890,"currency":"usd","net":-5390,"type":"adjustment","created":1438094525,"available_on":1438214400,"status":"pending","fee":1500,"fee_details":[{"amount":1500,"currency":"usd","type":"stripe_fee","description":"Dispute fee","application":null}],"source":"ch_16TZHIJMXBWnTQ4rmSoWK3kR","description":"Chargeback withdrawal for ch_16TZHIJMXBWnTQ4rmSoWK3kR","sourced_transfers":{"object":"list","total_count":0,"has_more":false,"url":"\/v1\/transfers?source_transaction=ad_16TZHNJMXBWnTQ4rRmeWFY38","data":[]}}],"evidence_details":{"due_by":1439510399,"past_due":false,"has_evidence":true,"submission_count":1},"evidence":{"product_description":"Order #172399 from restaurant DEVINS DRIVER TEST RESTAURANTz: \n- turkey.\n- bacon.\n- swiss.","customer_name":"MR TEST","customer_email_address":null,"customer_purchase_ip":"192.168.56.1","customer_signature":null,"billing_address":null,"receipt":null,"shipping_address":"4690 Eldorado Parkway, McKinney, TX 75070, USA","shipping_date":null,"shipping_carrier":null,"shipping_tracking_number":null,"shipping_documentation":null,"access_activity_log":null,"service_date":"Jul 28th 2015 7:42:00 AM PDT","service_documentation":null,"duplicate_charge_id":null,"duplicate_charge_explanation":null,"duplicate_charge_documentation":null,"refund_policy":null,"refund_policy_disclosure":null,"refund_refusal_explanation":null,"cancellation_policy":null,"cancellation_policy_disclosure":null,"cancellation_rebuttal":null,"customer_communication":null,"uncategorized_text":"Customer phone number: ***REMOVED***;\nUser Agent: Browser: chrome version(44.0), OS: macintosh","uncategorized_file":null},"metadata":{}},"previous_attributes":{"status":"needs_response","evidence_details":{"has_evidence":false,"submission_count":0},"evidence":{"product_description":null,"customer_name":null,"customer_purchase_ip":null,"shipping_address":null,"service_date":null,"uncategorized_text":null}}},"object":"event","pending_webhooks":1,"request":"req_6gxtsQciDKUF18","api_version":"2015-03-24"}' );
		// Crunchbutton_Stripe_Webhook::create( $data );
// die();
		// $settlement = new Crunchbutton_Settlement;
		// $settlement->payRestaurant( 41222 );

		// $settlement->doRestaurantsPayments();
		// Crunchbutton_Community::shutDownCommunities();

		// $dispute = Crunchbutton_Stripe_Dispute::o( 28 );
		// $dispute->autoSendEvidence();

// die( "hard" );

// charge.dispute.funds_withdrawn
		// Crunchbutton_Stripe_Dispute::create( 511 );
		// die( "hard" );

		$data = json_decode( '{"id":"evt_16TfwEJMXBWnTQ4rVJ9UhuBV","created":1438120122,"livemode":false,"type":"charge.dispute.created","data":{"object":{"id":"dp_16TfwDJMXBWnTQ4rO0KKoO3d","charge":"ch_16Tfw8JMXBWnTQ4rWb55D8UR","amount":6250,"created":1438120121,"status":"needs_response","livemode":false,"currency":"usd","object":"dispute","reason":"general","is_charge_refundable":false,"balance_transactions":[{"id":"txn_16TfwDJMXBWnTQ4rKjN0SeY3","object":"balance_transaction","amount":-6250,"currency":"usd","net":-7750,"type":"adjustment","created":1438120121,"available_on":1438214400,"status":"pending","fee":1500,"fee_details":[{"amount":1500,"currency":"usd","type":"stripe_fee","description":"Dispute fee","application":null}],"source":"ch_16Tfw8JMXBWnTQ4rWb55D8UR","description":"Chargeback withdrawal for ch_16Tfw8JMXBWnTQ4rWb55D8UR","sourced_transfers":{"object":"list","total_count":0,"has_more":false,"url":"\/v1\/transfers?source_transaction=ad_16TfwDJMXBWnTQ4rpYS9EXyZ","data":[]}}],"evidence_details":{"due_by":1439510399,"past_due":false,"has_evidence":false,"submission_count":0},"evidence":{"product_description":null,"customer_name":null,"customer_email_address":null,"customer_purchase_ip":null,"customer_signature":null,"billing_address":null,"receipt":null,"shipping_address":null,"shipping_date":null,"shipping_carrier":null,"shipping_tracking_number":null,"shipping_documentation":null,"access_activity_log":null,"service_date":null,"service_documentation":null,"duplicate_charge_id":null,"duplicate_charge_explanation":null,"duplicate_charge_documentation":null,"refund_policy":null,"refund_policy_disclosure":null,"refund_refusal_explanation":null,"cancellation_policy":null,"cancellation_policy_disclosure":null,"cancellation_rebuttal":null,"customer_communication":null,"uncategorized_text":null,"uncategorized_file":null},"metadata":{}}},"object":"event","pending_webhooks":1,"request":null,"api_version":"2015-03-24"}' );
		Crunchbutton_Stripe_Webhook::create( $data );
		die( "hard" );
// echo '<pre>';var_dump( $data );exit();

// echo '<pre>';var_dump( $data[ 'type' ] );exit();
// $data = get_object_vars( $data );
	// echo '<pre>';var_dump( $data );exit();
// echo '<pre>';var_dump( $data[ 'type' ] );exit();
		$type = Crunchbutton_Stripe_Webhook_Type::byType( $data[ 'type' ] );
		echo '<pre>';var_dump( $type );exit();

		echo '{"data":{"id":"evt_16SDQ8JMXBWnTQ4r9LUrRr9e","created":1437772172,"livemode":false,"type":"charge.succeeded","data":{"object":{"id":"ch_16SDQ8JMXBWnTQ4r4bUPVe3x","object":"charge","created":1437772172,"livemode":false,"paid":true,"status":"succeeded","amount":2710,"currency":"usd","refunded":false,"source":{"id":"card_16SCMzJMXBWnTQ4rZ5kLI2m0","object":"card","last4":"4242","brand":"Visa","funding":"credit","exp_month":2,"exp_year":2016,"fingerprint":"T2EUz7SWBO2RClZ2","country":"US","name":null,"address_line1":null,"address_line2":null,"address_city":null,"address_state":null,"address_zip":null,"address_country":null,"cvc_check":null,"address_line1_check":null,"address_zip_check":null,"tokenization_method":null,"dynamic_last4":null,"metadata":{},"customer":"cus_6eKZbxN8HwrBQU"},"captured":true,"balance_transaction":"txn_16SDQ8JMXBWnTQ4rGtyukeQf","failure_message":null,"failure_code":null,"amount_refunded":0,"customer":"cus_6eKZbxN8HwrBQU","invoice":null,"description":"DEVINS DRIVER TEST RESTAURANTz","dispute":null,"metadata":{},"statement_descriptor":"DEVINSDRIVERTESTRESTAU","fraud_details":{},"receipt_email":null,"receipt_number":null,"shipping":null,"destination":null,"application_fee":null,"refunds":{"object":"list","total_count":0,"has_more":false,"url":"\/v1\/charges\/ch_16SDQ8JMXBWnTQ4r4bUPVe3x\/refunds","data":[]}}},"object":"event","pending_webhooks":1,"request":"req_6fYX7SjAeczkYI","api_version":"2015-03-24"},"env":"dev"}';exit;
		echo '{"0":{"id":"evt_16SCN2JMXBWnTQ4rWTWRnNEi","created":1437768136,"livemode":false,"type":"charge.succeeded","data":{"object":{"id":"ch_16SCN2JMXBWnTQ4r8HI2vQcG","object":"charge","created":1437768136,"livemode":false,"paid":true,"status":"succeeded","amount":2710,"currency":"usd","refunded":false,"source":{"id":"card_16SCMzJMXBWnTQ4rZ5kLI2m0","object":"card","last4":"4242","brand":"Visa","funding":"credit","exp_month":2,"exp_year":2016,"fingerprint":"T2EUz7SWBO2RClZ2","country":"US","name":null,"address_line1":null,"address_line2":null,"address_city":null,"address_state":null,"address_zip":null,"address_country":null,"cvc_check":null,"address_line1_check":null,"address_zip_check":null,"tokenization_method":null,"dynamic_last4":null,"metadata":{},"customer":"cus_6eKZbxN8HwrBQU"},"captured":true,"balance_transaction":"txn_16SCN2JMXBWnTQ4rzhVuGPb1","failure_message":null,"failure_code":null,"amount_refunded":0,"customer":"cus_6eKZbxN8HwrBQU","invoice":null,"description":"DEVINS DRIVER TEST RESTAURANTz","dispute":null,"metadata":{},"statement_descriptor":"DEVINSDRIVERTESTRESTAU","fraud_details":{},"receipt_email":null,"receipt_number":null,"shipping":null,"destination":null,"application_fee":null,"refunds":{"object":"list","total_count":0,"has_more":false,"url":"\/v1\/charges\/ch_16SCN2JMXBWnTQ4r8HI2vQcG\/refunds","data":[]}}},"object":"event","pending_webhooks":1,"request":"req_6fXRKmKNcgLmUE","api_version":"2015-03-24"},"env":"dev"}';exit;
		echo '{"0":{"id":"evt_16SCN1JMXBWnTQ4rzNkdL1EE","created":1437768135,"livemode":false,"type":"customer.source.created","data":{"object":{"id":"card_16SCMzJMXBWnTQ4rZ5kLI2m0","object":"card","last4":"4242","brand":"Visa","funding":"credit","exp_month":2,"exp_year":2016,"fingerprint":"T2EUz7SWBO2RClZ2","country":"US","name":null,"address_line1":null,"address_line2":null,"address_city":null,"address_state":null,"address_zip":null,"address_country":null,"cvc_check":null,"address_line1_check":null,"address_zip_check":null,"tokenization_method":null,"dynamic_last4":null,"metadata":{},"customer":"cus_6eKZbxN8HwrBQU"}},"object":"event","pending_webhooks":1,"request":"req_6fXR3zNRAXRYn3","api_version":"2015-03-24"},"env":"dev"}';exit;
	}
}

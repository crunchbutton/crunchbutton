<?php

class Controller_Api_Test_Sandbox extends Cana_Controller {

	public function init(){
		$settlement = new Settlement;
			$summary = $settlement->driverSummary( 23614 );
			// echo json_encode( $summary );exit;
		// echo json_encode( $summary );exit();
		$mail = new Crunchbutton_Email_Payment_Summary( [ 'summary' => $summary ] );
		echo $mail->message();
		exit;

		// Crunchbutton_Support::lastAutoReplyByPhone( '2404410108' );


		die('hard');

		$est_labels = [];
		$est_labels[] = 'est: +1d';
		$est_labels[] = 'est: +1h';
		$est_labels[] = 'est: +2d';
		$est_labels[] = 'est: +2h';
		$est_labels[] = 'est: +3d';
		$est_labels[] = 'est: +3h';
		$est_labels[] = 'est: +4d';
		$est_labels[] = 'est: +4h';
		$est_labels[] = 'est: +5d';
		$est_labels[] = 'est: +5h';
		$est_labels[] = 'est: +6h';
		$est_labels[] = 'est: +7h';
		$est_labels[] = 'est: +8h';
		$est_labels[] = 'est: +10m';
		$est_labels[] = 'est: +15m';
		$est_labels[] = 'est: +30m';
		$est_labels[] = 'est: +45m';
		// echo '<pre>';var_dump( $est_labels );exit();

		$client = new \Github\Client( new \Github\HttpClient\CachedHttpClient(array('cache_dir' => '/tmp/github-api-cache')) );
		$client->authenticate( 'pererinha', 'talentiousio!3', Github\Client::AUTH_HTTP_PASSWORD );

		$params = array( 'labels' => 'waffle: estimate', 'page' => 1, 'state' => 'open', 'per_page' => 100 );


		$issues = $client->api('issue')->all( 'crunchbutton', 'crunchbutton', $params );
		echo json_encode( $issues );exit;
		// echo '<pre>';var_dump( $issues );exit();
	}
}
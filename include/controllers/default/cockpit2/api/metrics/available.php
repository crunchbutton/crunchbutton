<?php
// tells front end which charts are available and how to display them
class Controller_api_metrics_available extends Crunchbutton_Controller_RestAccount {
	public function init() {
		echo json_encode(Cockpit_Metrics::availableMetrics());
	}
}
?>

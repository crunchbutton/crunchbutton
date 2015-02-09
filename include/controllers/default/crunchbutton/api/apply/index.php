<?php

class Controller_api_apply extends Crunchbutton_Controller_Rest {
	public function init() {
		// Log the order - process started
			$request = new \Cana_Curl('https://www.playbookhr.com/api/external/applicants', 
				['name' => $this->request()['firstName'] , 'Phone Type' => $this->request()['phoneType'],
				 'Last Name' => $this->request()['lastName'],
				 'City' => $this->request()['university'],
				 'Phone Number' => $this->request()['number'],
				 'email' => $this->request()['email'],
				 'Phone Carrier' => $this->request()['otherCarrier'] ?  $this->request()['otherCarrier'] :  $this->request()['carrier'],
				 'Car / Bike' => $this->request()['transport'],
				 'No. Hours / Week' => $this->request()['hours'],
				 'Student vs Non-Student' => $this->request()['applicant'],
				 'Source' => $this->request()['otherSource'] ?  $this->request()['otherSource'] :  $this->request()['source']

				], 'post', null, null, null, 
				['user' => 'crunchbutton','pass' => 'f59FEmNH44koOU6wI44iGEMkW8Oms0mg']);
			print_r($request->output);
			exit();
		switch ( $this->method() ) {
			case 'post':
				$request = new \Cana_Curl('https://www.playbookhr.com/api/external/applicants', ['name' => $this->request()['firstName']], 'post', null, null, null, ['user' => 'playbookhr','pass' => '1DB5DeodDyEaskEUi4ISdEciWggk8aKa']);
				echo json_encode(['success' => 'success']);
			break;
		
			default:
				echo json_encode(['error' => 'invalid request']);
			break;
		}
	}
}
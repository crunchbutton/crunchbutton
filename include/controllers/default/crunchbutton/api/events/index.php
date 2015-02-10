<?php


class Crunchbutton_Analytics_Event extends Cana_Table {
	public static function buildEvent($category, $action, $label = null, $community = null, $data = null) {
		$me = new Crunchbutton_Analytics_Event();
		$me->category = $category;
		$me->action = $action;
		$me->label = $label;
		$me->ts = date('Y-m-d H:i:s');
		if(!is_null($data)) {
			$me->json_data = json_encode($data);
		}
		$session = c::auth()->session();
		if($session) {
			$me->id_session = $session->id_session;
			$me->id_user = $session->id_user;
		}
		$headers = getallheaders();
		// TODO: replace with actual references to agent table.
		if($headers['User-Agent']) {
			$me->user_agent = $headers['User-Agent'];
		}
		if($headers['REMOTE_ADDR']) {
			$me->ip = $headers['REMOTE_ADDR'];
		}
		return $me;
	}
	public static function storeEvent($category, $action, $label = null, $community = null, $data = null) {
		$me = Crunchbutton_Analytics_Event::buildEvent($category, $action, $label, $community, $data);
		$me->save();
		return $me;
	}
	public function __construct() {
		$this->_table = 'analytics_event';
		$this->_id_var = 'id_analytics_event';
		parent::__construct();
	}

}

// Event API expects every request to have a category, action and potentially a label. 
// Requests may also send an additional blob of data that may or may not be used. category
// and action are included in request URLs to facilitate grepping through logs on storage errors.
//
// GET /api/events?category=page&action=appload&community=5
// Response 201 (application/json) - {'id_analytics_event': <some_id>} indicates success
// Response 500 - server error (e.g., overloaded, etc.)
// Response 400 (application/json) - missing category or action
// {'error': 'missing category'} 
//
// POST /api/events?category=page&action=ordered&label=bought+dat
// Body (application/json)
// {"id_dish": 10, "price": 15.2}
// Response 201 (application/json) -  {'id_analytics_event': <some_id>} indicates success
// Response 400 (application/json) - missing category or action
// Response 422 (unprocessable entity) - request included invalid JSON data
// Response 500 (e.g., overloaded, etc.)
class Controller_api_events extends Crunchbutton_Controller_Rest {
    public function init() {
    	$category = $_REQUEST['category'];
    	$label = $_REQUEST['label'];
    	$action = $_REQUEST['action'];
    	$community = $_REQUEST['community'];
    	if(!$category) {
    		// 400 here
    		echo json_encode(["error" => "category is required"]);
    	}
    	if(!$action) {
    		echo json_encode(["error" => "action is required"]);
    	}
    	if($this->method() == 'post') {
    		$data = $this->request();
    	}
    	$resp = Crunchbutton_Analytics_Event::storeEvent($category, $action, $label, $community, $data);
    	// TODO: Ask Devin about which function sets content type to application/json
    	echo json_encode(["id_analytics_event" => $resp->id_analytics_event]);
    }
}
?>

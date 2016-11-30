<?php

class Crunchbutton_Email extends Cana_Model {

	public function __construct($params) {
		$this->to = $params['to'];
		$this->from = $params['from'];
		$this->subject = $params['subject'];
		$this->html = $params['messageHtml'];
		$this->reason = $params['reason'];
	}

	public function buildView($params) {
		$params['theme'][] = 'mail/'.c::config()->defaults->theme.'/';
		$params['layout'] =  c::config()->defaults->layout;
		$params['base'] = c::config()->dirs->view;
		$view = new Cana_View($params);
		$this->view($view);
	}

	public function send() {

		if (!filter_var($this->to, FILTER_VALIDATE_EMAIL)) {
			return false;
		}

		$res = c::mailgun()->sendMessage(c::config()->mailgun->domain, [
			'from' 		=> $this->from,
			'to'		=> $this->to,
			'subject'	=> $this->subject,
			'html'		=> $this->html
		]);

		$status = ($res && $res->http_response_body && $res->http_response_body->id) ? true : false;

		if (!$status) {
			Log::debug([
				'action' => 'send email',
				'to' => $this->to,
				'from' => $this->from,
				'subject' => $this->subject,
				'type' => 'notification',
				'status' => $status ? 'success' : 'failed',
				'response' => json_encode($res)
			]);
		}

		Crunchbutton_Email_Address_Log::log( [ 'email_address_to' => $this->to, 'email_address_from' => $this->from, 'subject' => $this->subject, 'reason' => $this->reason ] );

		return $status;
	}

	public function message() {
		return $this->html;
	}


	public function view($view = null) {
		if (is_null($view)) {
			return $this->_view;
		} else {
			$this->_view = $view;
		}
	}
}
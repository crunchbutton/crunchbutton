<?php

class Cockpit_Travis {
	private $_url = 'https://api.travis-ci.com/';
	private $_token = null;

	public function __construct() {

	}
	
	public function status($user, $repo, $sha = null) {

		$res = $this->request('repos/'.$user.'/'.$repo.'/builds');
		
		$commits = [];
		$ret = [];
		
		foreach ($res->commits as $item) {
			$commits[$item->id] = [
				'commit' => $item->sha
			];
		}
		
		foreach ($res->builds as $item) {
			$commits[$item->commit_id]['status'] = $item->state;
		}
		
		foreach ($commits as $commit) {
			$ret[$commit['commit']] = $commit['status'];
		}
		
		return $sha ? $ret[$sha] : $ret;
	}

	private function request($url, $data = [], $method = 'GET') {
		if (!$this->_token) {
			$res = $this->_request('auth/github', ['github_token' => c::config()->github->token], 'POST');
			$this->_token = $res->access_token;
		}

		return $this->_request($url, $data, $method);
	}


	private function _request($url, $data = [], $method = 'GET') {

		$data = json_encode($data);

		$ch = curl_init($this->_url.$url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$headers = [
			'Accept: application/vnd.travis-ci.2+json',
			'User-Agent: Crunchbutton/Cockpit'
		];
		
		if ($method == 'POST') {
			$headers[] = 'Content-Type: application/json';
			$headers[] = 'Content-Length: ' . strlen($data);
		}
		
		if ($this->_token) {
			$headers[] = 'Authorization: token "'.$this->_token.'"';
		}
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$res = curl_exec($ch);
		curl_close($ch);

		return json_decode($res);
	}
}


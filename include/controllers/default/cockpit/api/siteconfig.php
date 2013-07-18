<?php

class Controller_api_siteconfig extends Crunchbutton_Controller_RestAccount {
	public function init() {
		switch ($this->method()) {
			case 'post':
				if (is_array($this->request()['key']) && is_array($this->request()['value'])) {
					foreach ($this->request()['key'] as $k => $key) {
						c::config()->site->config($key)->set($this->request()['value'][$k]);
					}

				} elseif ($this->request()['key'] && $this->request()['value']) {
					c::config()->site->config($this->request()['key'])->set($this->request()['value']);
				}
				break;

			case 'get':
				echo json_encode(c::config()->site->exportConfig());
				break;
		}
	}
}

<?php

class Controller_api_siteconfig extends Crunchbutton_Controller_RestAccount {
	public function init() {
		switch ($this->method()) {
			case 'post':
				if (is_array($this->request()['key']) && is_array($this->request()['value'])) {
					foreach ($this->request()['key'] as $k => $key) {
						$this->save( $key, $this->request()['value'][$k] );
					}

				} elseif ($this->request()['key'] && $this->request()['value']) {
					$this->save( $this->request()['key'], $this->request()['value'] );
				}
				break;

			case 'get':
				echo json_encode(c::config()->site->exportConfig());
				break;
		}
	}

	private function save( $key, $value ){

		$hasPermission = c::admin()->permission()->check( [ 'global' ] );

		if( !$hasPermission ){
			switch ( $key ) {
				case 'support-phone-afterhours':
					$hasPermission = c::admin()->permission()->check( [ 'global', 'support-all', 'support-settings' ] );
					break;
				case 'referral-inviter-credit-value':
				case 'referral-invited-credit-value':
				case 'referral-add-credit-to-invited':
				case 'referral-limit-per-code':
				case 'referral-is-enable':
				case 'referral-invites-limit-per-code':
					$hasPermission = c::admin()->permission()->check( [ 'global', 'invite-promo' ] );
				break;
			}
		}

		if( $hasPermission ){
			c::config()->site->config($key)->set($value);
		}
	}

}

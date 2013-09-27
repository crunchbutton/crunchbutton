<?php

/**
 * Access control list
 *
 * @author	Devin Smith <devins@devin-smith.com>
 * @date	2009.10.20
 *
 */


class Crunchbutton_Acl_Admin extends Crunchbutton_Acl_Base {
	public function __construct(Crunchbutton_Admin $admin) {
		$this->_table = 'admin_permission';
		parent::__construct($admin);
	}
}
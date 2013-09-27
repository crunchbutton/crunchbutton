<?php

/**
 * Access control list
 *
 * @author	Devin Smith <devins@devin-smith.com>
 * @date	2009.10.20
 *
 */


class Crunchbutton_Acl_Base extends Cana_Model {

	public $_permissions;
	public $_userPermission;
	public $_admin;

	public function __construct(Crunchbutton_Admin $admin, $params = []) {
		$this->_admin = $admin;

		$this
			->popGroupPermissions()
			->popUserPermissions();
	}
	
	private function popGroupPermissions() {
		if ($this->_admin->id_admin) {
			$res = Cana::db()->query('
				SELECT p.* FROM '.$this->_table.' p
				LEFT JOIN admin_group g ON g.id_group=p.id_group
				WHERE
					(g.id_admin="'.$this->_admin->id_admin.'"
					AND p.id_admin IS NULL)
					OR (p.id_group="ALL" AND p.id_admin IS NULL)
			');
			while($row = $res->fetch()) {
				$this->_permissions[$row->id_group][strtoupper($row->permission)][] = $row->allow ? true : false;
			}
		}
		
		return $this;
	}

	private function popUserPermissions() {
		$res = Cana::db()->query('
			SELECT p.* FROM '.$this->_table.' p
			WHERE
				(
					p.id_admin'.($this->_admin->id_admin ? ('="'.$this->_admin->id_admin.'"') : ' IS NULL ').'
					OR p.id_admin IS NULL
				)
				AND p.id_group IS NULL
		');
		while($row = $res->fetch()) {
			$this->_userPermission[strtoupper($row->permission)][] = $row->allow ? true : false;
		}
		return $this;	
	}
	
	private function checkItem($action) {
		// all actions and permissions are upper case
		$action = strtoupper($action);
		
		// check the staff members group for permissions
		if (isset($this->_permissions)) {
			foreach ($this->_permissions as $id_group => $groupPermissions) {
				if (isset($groupPermissions[$action])) {
					foreach ($groupPermissions[$action] as $act) {
						$pass = (!isset($pass) || $act) ? $act : $pass;
					}
				}
			}
		}

		// check the staff members permissions
		if (isset($this->_userPermission[$action])){
			foreach ($this->_userPermission[$action] as $act) {
				$pass = $act;
			}
		}
		return isset($pass) ? $pass : false;
	
	}
	
	public function check($action) {
		if (is_array($action)) {
			$pass = false;
			foreach ($action as $act) {
				$actPass = $this->checkItem($act);
				$pass = ($actPass || $pass) ? true : false;
			}
		} else {
			$pass = $this->checkItem($action);
		}

		return $pass;
	}

}
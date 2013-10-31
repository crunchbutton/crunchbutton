<?php

class Crunchbutton_Admin_Permission extends Cana_Table {

	public function __construct($id = null) {
		
		parent::__construct();

		$_elements = array( 
												'Restaurant' => c::db()->get( 'SELECT id_restaurant AS id, name AS name FROM restaurant WHERE name IS NOT NULL ORDER BY name ASC' ),
												'Community' => c::db()->get( "SELECT DISTINCT( REPLACE( LOWER( community ), ' ', '-' ) ) AS id, community AS name FROM restaurant WHERE community IS NOT NULL ORDER BY community ASC" ),
											);
			
		$_permissions = array();

		/* Global's permissions */
		$_permissions[ 'global' ] = array( 'description' => 'Global' );
		$_permissions[ 'global' ][ 'permissions' ] = array( 'global' => array( 'description' => 'Could do any action at cockpit.' ) );

		/* Restaurants's permissions */
		$_permissions[ 'restaurant' ] = array( 'description' => 'Restaurant\'s permissions' );
		$_permissions[ 'restaurant' ][ 'permissions' ] = array( 
																											'restaurants-all' => array( 'description' => 'Do all about the restaurants' ),
																											'restaurants-list-page' => array( 'description' => 'View the restaurant\'s list page' ),
																											'restaurants-crud' => array( 'description' => 'Create, update, retrieve and delete any restaurant' ),
																											'restaurant-ID-all' => array( 'description' => 'Do all about the restaurant ID', 'type' => 'combo', 'element' => 'Restaurant', 'dependency' => array( 'restaurants-list-page' ) ),
																											'restaurant-ID-edit' => array( 'description' => 'Edit the info about the restaurant ID, it does not include payment and send fax', 'type' => 'combo', 'element' => 'Restaurant', 'dependency' => array( 'restaurants-list-page' ) ),
																											'restaurant-ID-pay' => array( 'description' => 'Make the payment of the restaurant ID', 'type' => 'combo', 'element' => 'Restaurant', 'dependency' => array( 'restaurants-list-page' ) ),
																											'restaurant-ID-fax' => array( 'description' => 'Send fax to the restaurant ID', 'type' => 'combo', 'element' => 'Restaurant', 'dependency' => array( 'restaurants-list-page' ) ),
																											'restaurants-weight-adj-page' => array( 'description' => 'View the adjustment weight page, the user will be able to edit just the restaurant he has the permission', 'dependency' => array( 'restaurants-list-page' ) ),
																										);

		/* Orders's permissions */ 
		$_permissions[ 'order' ] = array( 'description' => 'Orders\'s permissions' );
		$_permissions[ 'order' ][ 'permissions' ] = array( 
																											'orders-all' => array( 'description' => 'Do all about orders' ),
																											'orders-list-page' => array( 'description' => 'View the order\'s list page' ),
																											'orders-list-restaurant-ID' => array( 'description' => 'View the orders of the restaurant ID', 'type' => 'combo', 'element' => 'Restaurant', 'dependency' => array( 'orders-list-page' ) ),
																											'orders-new-users' => array( 'description' => 'View new users page', 'dependency' => array( 'orders-list-page' ) ),
																											'orders-notification' => array( 'description' => 'Send notification', 'dependency' => array( 'orders-list-page' ) ),
																											'orders-refund' => array( 'description' => 'Refund orders', 'dependency' => array( 'orders-list-page' ) ),
																											'orders-export' => array( 'description' => 'Export orders', 'dependency' => array( 'orders-list-page' ) ),
																										);

		/* Gift card's permissions */ 
		$_permissions[ 'giftcard' ] = array( 'description' => 'Gift card\'s permissions' );
		$_permissions[ 'giftcard' ][ 'permissions' ] = array( 
																											'gift-card-all' => array( 'description' => 'Do all about gift cards' ),
																											'gift-card-list-page' => array( 'description' => 'View the list page' ),
																											'gift-card-list-restaurant-ID' => array( 'description' => 'View the gift cards of the restaurant ID', 'type' => 'combo', 'element' => 'Restaurant' ),
																											'gift-card-list-all' => array( 'description' => 'View gift cards from all the restaurants' ),
																											'gift-card-create' => array( 'description' => 'Create gift card' ),
																											'gift-card-create-all' => array( 'description' => 'Create gift cards to any restaurant' ),
																											'gift-card-create-restaurant-ID' => array( 'description' => 'Create gift cards to restaurant ID', 'dependency' => array( 'gift-card-create' ), 'type' => 'combo', 'element' => 'Restaurant' ),
																											'gift-card-groups' => array( 'description' => 'Manage gift card groups' ),
																											'gift-card-restaurant-ID' => array( 'description' => 'Create and list gift card for the restaurant ID', 'type' => 'combo', 'element' => 'Restaurant' ),
																											'gift-card-delete' => array( 'description' => 'Delete or remove the credits from a gift card' ),
																											'gift-card-anti-cheat' => array( 'description' => 'View the gift card anti cheat page' ),
																										);

		/* Metric's permissions */ 
		$_permissions[ 'metrics' ] = array( 'description' => 'Metric\'s permissions' );
		$_permissions[ 'metrics' ][ 'permissions' ] = array( 
																											'metrics-all' => array( 'description' => 'View all metrics' ),
																											'metrics-main' => array( 'description' => 'View the `Main` charts' ),
																											'metrics-investors' => array( 'description' => 'View the `For Investor` charts' ),
																											'metrics-detailed-analytics' => array( 'description' => 'View the `Detailed Analytics` charts' ),
																											'metrics-no-grouped-charts' => array( 'description' => 'View the `No grouped` and `Old Graphs` charts' ),
																											'metrics-communities-all' => array( 'description' => 'View the all communities metrics.' ),
																											'metrics-communities-page' => array( 'description' => 'View the all communities metrics.' ),
																											'metrics-communities-ID' => array( 'description' => 'See the metrics of the community ID', 'dependency' => array( 'metrics-communities-page' ), 'type' => 'combo', 'element' => 'Community' ),
																											'metrics-manage-cohort' => array( 'description' => 'Manage the cohorts' ),
																										);

		/* Support's permissions */ 
		$_permissions[ 'support' ] = array( 'description' => 'Support\'s permissions' );
		$_permissions[ 'support' ][ 'permissions' ] = array( 
																											'support-all' => array( 'description' => 'Do all about support' ),
																											'support-crud' => array( 'description' => 'Create, update and delete any support ticket' ),
																											'support-view' => array( 'description' => 'View the support page' ),
																											'support-settings' => array( 'description' => 'Change support setting' ),
																										);

		/* Suggestions's permissions */ 
		$_permissions[ 'suggestion' ] = array( 'description' => 'Suggestions\'s permissions' );
		$_permissions[ 'suggestion' ][ 'permissions' ] = array( 
																											'suggestions-all' => array( 'description' => 'Do all about suggestions' ),
																											'suggestions-list-page' => array( 'description' => 'View suggestions page' ),
																											'suggestions-list-restaurant-ID' => array( 'description' => 'View the food suggestions for the restaurant ID', 'dependency' => array( 'suggestions-list-page' ), 'type' => 'combo', 'element' => 'Restaurant' ),
																										);

		/* Other's permissions */ 
		$_permissions[ 'permissions' ] = array( 'description' => 'Admin user\'s permissions' );
		$_permissions[ 'permissions' ][ 'permissions' ] = array( 
																										'permission-all' => array( 'description' => 'Do all about admin users and groups (create, update, delete, assign permissions)' ),
																										'permission-users' => array( 'description' => 'Do all about admin users (create, update, delete, assign permissions)' ),
																										'permission-groups' => array( 'description' => 'Do all about admin groups (create, update, delete, assign permissions)' ),
																										);

		/* Other's permissions */ 
		$_permissions[ 'other' ] = array( 'description' => 'Other\'s permissions' );
		$_permissions[ 'other' ][ 'permissions' ] = array( 
																										'github' => array( 'description' => 'View github page and issues' ),
																										'customers-all' => array( 'description' => 'Do all about customers' ),
																										'curation' => array( 'description' => 'View curation page' ),
																										'locations' => array( 'description' => 'View the locations page' ),
																										'marketing-events' => array( 'description' => 'View the marketing events page' ),
																										'logs' => array( 'description' => 'View the logs page' ),
																										'invite-promo' => array( 'description' => 'View and edit invite promo settings' ),
																										);

		$this->_permissions = $_permissions;
		$this->_elements = $_elements;

		$this
			->table('admin_permission')
			->idVar('id_admin_permission')
			->load($id);
	}

	public function elements(){
		return $this->_elements;
	}

	public function all(){
		return $this->_permissions;
	}

	public function getPermissionInfo( $permission ){
		$all_permissions = $this->_permissions;
		foreach( $all_permissions as $group ){
			$permissions = $group[ 'permissions' ];
			foreach( $permissions as $key => $val ){
				$regex = str_replace( 'ID' , '(.)*', $key );
				$regex = '/' . $regex . '/';
				if( preg_match( $regex, $permission ) > 0 ){
					return $val;
				}
			}
		}
		return false;
	}

	public function getDependency( $permission ){
		$info = $this->getPermissionInfo( $permission );
		if( $info ){
			if( $info[ 'dependency' ] ){
				return $info[ 'dependency' ];
			}
		}
		return false;
	}
}
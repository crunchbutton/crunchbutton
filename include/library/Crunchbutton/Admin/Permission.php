<?php

class Crunchbutton_Admin_Permission extends Cana_Table {

	public function __construct($id = null) {
		
		parent::__construct();

		$_elements = array( 'Restaurant' => c::db()->get( 'SELECT id_restaurant AS id, name AS name FROM restaurant WHERE name IS NOT NULL ORDER BY name ASC' ) );
			
		$_permissions = array();

		$_permissions[ 'restaurant' ] = array( 'description' => 'Restaurant`s permissions' );
		$_permissions[ 'restaurant' ][ 'permissions' ] = array( 
																											'restaurants-all' => array( 'description' => 'do all about the restaurants' ),
																											'restaurants-list-page' => array( 'description' => 'view the restaurant`s list page' ),
																											'restaurants-crud' => array( 'description' => 'create, update, retrieve and delete any restaurant' ),
																											'restaurant-ID-all' => array( 'description' => 'do all about the restaurant ID', 'type' => 'combo', 'element' => 'Restaurant', 'dependency' => array( 'restaurants-list-page' ) ),
																											'restaurant-ID-edit' => array( 'description' => 'edit the info about the restaurant ID, it does not include payment and send fax', 'type' => 'combo', 'element' => 'Restaurant', 'dependency' => array( 'restaurants-list-page' ) ),
																											'restaurant-ID-pay' => array( 'description' => 'make the payment of the restaurant ID', 'type' => 'combo', 'element' => 'Restaurant', 'dependency' => array( 'restaurants-list-page' ) ),
																											'restaurant-ID-fax' => array( 'description' => 'send fax to the restaurant ID', 'type' => 'combo', 'element' => 'Restaurant', 'dependency' => array( 'restaurants-list-page' ) ),
																											'restaurants-weight-adj-page' => array( 'description' => 'view the adjustment weight page, the user will be able to edit just the restaurant he has the permission', 'dependency' => array( 'restaurants-list-page' ) ),
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
				if( $key == $permission ){
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


/*
** This permission must to be combined with `restaurants-list-page`

### Orders
* `orders-all`: do all about orders
* `orders-list-page	`: view the restaurants page
* `orders-list-restaurant-ID`: view the orders of the restaurant **ID** **
* `orders-new-users`: view new users page **
* `orders-notification`: send notification **
* `orders-refund`: refund orders **
* `orders-export`: export orders **

** This permission must to be combined with `orders-list-page`

### Gift card

* `gift-card-all`: do all about gift cards
* `gift-card-list-page`: view the list page
* `gift-card-list-restaurant-ID	`: view the gift cards of the restaurant **ID**
* `gift-card-list-all`: view gift cards from all the restaurants
* `gift-card-create	`: create gift card
* `gift-card-create-all	`: create gift cards to any restaurant
* `gift-card-create-restaurant-ID`: create gift cards to restaurant **ID** **
* `gift-card-groups`:	manage gift card groups
* `gift-card-restaurant-ID`: create and list gift card for the restaurant **ID**
* `gift-card-delete`: delete or remove the credits from a gift card
* `gift-card-anti-cheat`: view the gift card anti cheat page

** This permission must to be combined with `gift-card-create`

### Metrics

* `metrics-all`: view all metrics 
* `metrics-main`: view the **Main** charts
* `metrics-investors`: view the **For Investor** charts
* `metrics-detailed-analytics`: view the **Detailed Analytics** charts
* `metrics-no-grouped-charts`: view the **No grouped** and **Old Graphs** charts
* `metrics-communities-all`: view the all communities metrics. 
* `metrics-communities-page`: view the all communities metrics. 
* `metrics-communities-COMMUNITY`: see the metrics of the **COMMUNITY** **
* `metrics-manage-cohort:`: manage the cohorts

** The community name at the permission should be at smallcaps and replace space per dashes. Example the 'New York City' community's permission would be `metrics-communities-new-york-city`. This permission must to be combined with `metrics-communities-page`.

### Support

* `support-all`: do all about support
* `support-crud`: create, update and delete any support ticket
* `support-view	`: view the support page
* `support-settings	`: change support setting

### Suggestions

* `suggestions-all`: do all about suggestions
* `suggestions-list-page`: view suggestions page
* `suggestions-list-restaurant-ID`: view the food suggestions for the restaurant **ID** **

** This permission must to be combined with `suggestions-list-page`

### Others
* `github`: view github page and issues
* `customers-all`: do all about customers
* `curation`: view curation page
* `locations`: view the locations page
* `marketing-events	`: view the marketing events page
* `logs`: view the logs page
* `invite-promo`: view and edit invite promo settings
*/
}
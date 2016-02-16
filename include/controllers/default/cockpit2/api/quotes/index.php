<?php

class Controller_api_quotes extends Crunchbutton_Controller_Rest {

	public function init() {

		switch ( $this->method() ) {
			case 'get':
				$this->_get();
			break;
			case 'post':
				$this->_permission();
				$this->_post();
			break;
		}
	}

	private function _permission(){
		if (!c::admin()->permission()->check(['global', 'quote-all' ])) {
			$this->error(401, true);
		}
	}

	private function _get(){

		switch ( c::getPagePiece( 2 ) ) {

			case 'list':
				$this->_permission();
				$this->_list();
				break;

			default:
				$quote = Crunchbutton_Quote::o( c::getPagePiece( 2 ) );
				if( $quote->id_quote ){
					echo $quote->json(); exit();
				}
				$this->_error();
				break;
		}
	}

	private function _post(){
		$this->_save();
	}

	private function _save(){

		if( $this->request()[ 'id_quote' ] ){
			$quote = Crunchbutton_Quote::o( $this->request()[ 'id_quote' ] );
		}
		if( !$quote->id_quote ){
			$quote = new Crunchbutton_Quote;
		}

		$quote->id_admin = c::user()->id_admin;
		$quote->name = $this->request()[ 'name' ];
		$quote->title = $this->request()[ 'title' ];
		$quote->facebook_id = $this->request()[ 'facebook_id' ];
		$quote->quote = $this->request()[ 'quote' ];
		$quote->all = ( $this->request()[ 'all' ] ? 1 : 0 );
		$quote->all_restaurants = ( $this->request()[ 'all_restaurants' ] ? 1 : 0 );
		$quote->active = ( $this->request()[ 'active' ] ? 1 : 0 );
		$quote->pages = ( $this->request()[ 'pages' ] ? 1 : 0 );
		$quote->date = date( 'Y-m-d H:i:s' );
		$quote->save();

		Crunchbutton_Quote_Community::removeByQuote( $quote->id_quote );

		if( !$quote->all ){
			$communities = $this->request()[ 'communities' ];
			foreach( $communities as $id_community ){
				$community = new Crunchbutton_Quote_Community();
				$community->id_quote = $quote->id_quote;
				$community->id_community = $id_community;
				$community->save();
			}
		}

		Crunchbutton_Quote_Restaurant::removeByQuote( $quote->id_quote );

		if( !$quote->all_restaurants ){
			$restaurants = $this->request()[ 'restaurants' ];
			foreach( $restaurants as $id_restaurant ){
				$restaurant = new Crunchbutton_Quote_Restaurant();
				$restaurant->id_quote = $quote->id_quote;
				$restaurant->id_restaurant = $id_restaurant;
				$restaurant->save();
			}
		}

		echo json_encode( [ 'id_quote' => $quote->id_quote ] );
		exit;
	}

	private function _list(){

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$community = $this->request()['community'] ? $this->request()['community'] : null;
		if( $community == 'all' ){
			$community = null;
		}
		$keys = [];

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}

		$q = '
			SELECT
				-WILD-
			FROM quote q
		';
		if ($community) {
			$q .= '
				LEFT JOIN quote_community qc ON q.id_quote=qc.id_quote
			';
		}
		$q .='
			WHERE
				q.name IS NOT NULL
		';


		if ($community) {
			$q .= '
				AND qc.id_community=?
			';
			$keys[] = $community;
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'q.name' => 'like',
					'q.title' => 'like',
					'q.quote' => 'like'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}

		// get the count
		$count = 0;
		$r = c::db()->query(str_replace('-WILD-','COUNT(*) c', $q), $keys);
		while ($c = $r->fetch()) {
			$count++;
		}

		$q .= '
			ORDER BY q.name ASC
			LIMIT '.intval($limit).'
			OFFSET '.intval($offset).'
		';

		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','
			q.*
		', $q), $keys);

		while ($s = $r->fetch()) {
			$quote = Crunchbutton_Quote::o($s);
			$out = $quote->exports();
			$out['communities'] = [];
			foreach ($quote->communities( true ) as $community) {
				$out['communities'][] = [ 'id_community' => $community->id_community, 'name' => $community->name ];
			}
			$out['active'] = ( intval( $out[ 'active'] ) ) ? true : false;
			$data[] = $out;
		}

		echo json_encode([
			'count' => intval($count),
			'pages' => ceil($count / $limit),
			'page' => $page,
			'results' => $data
		]);

	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}

}

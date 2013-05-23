<?php


class Controller_issues extends Crunchbutton_Controller_Account {
	public function init() {

		$client = new Github\Client(
			new Github\HttpClient\CachedHttpClient(array('cache_dir' => '/tmp/github-api-cache'))
		);
		
		function getWeight($item) {
			foreach ($item['labels'] as $label) {
				switch ($label['name']) {
					case 'PRIO: LO':
						return 3;
						break;
					case 'PRIO: CBB':
						return 4;
						break;
					case 'PRIO: HI':
						return 1;
						break;
					case 'PRIO: ASAP':
						return 0;
						break;
					case 'PRIO: MED':
						return 2;
						break;
				}
			}
			return 5;
		};
		
		function issueSort($a, $b) {
			return strcmp(getWeight($a), getWeight($b));
		};

		switch (c::getPagePiece(1)) {

			case 'view':
				if (!$_COOKIE['github-token']) {
					header('Location: /issues');
					exit;
				}

				$res = $client->authenticate($_COOKIE['github-token'], null, Github\Client::AUTH_HTTP_TOKEN);
				$res = $client->getHttpClient()->get('repos/crunchbutton/crunchbutton/assignees');
				$users = $res->getContent();

				foreach ($users as $user) {
					$res = $client->getHttpClient()->get('repos/crunchbutton/crunchbutton/issues?state=open&assignee='.$user['login']);
					$issues[$user['login']] = $res->getContent();
					
					usort($issues[$user['login']], 'issueSort');
				}

				c::view()->users = $users;
				c::view()->issues = $issues;
				c::view()->display('issues/view');

				break;
				
			case 'search':
				if (!$_COOKIE['github-token']) {
					header('Location: /issues');
					exit;
				}

				if( $_REQUEST['date'] ){
					$dateLimit = strtotime( $_REQUEST['date'] );
					$res = $client->authenticate($_COOKIE['github-token'], null, Github\Client::AUTH_HTTP_TOKEN);

					$limitOfPages = 3;
					$issuesPerPage = 100;

					$issues = array();
					for( $page = 1; $page <= $limitOfPages; $page++ ){
						$res = $client->getHttpClient()->get( 'repos/crunchbutton/crunchbutton/issues?filter=all&state=closed&sort=updated&updated=>2013-05-13&per_page=' . $issuesPerPage . '&page=' . $page );
						$issues = array_merge( $issues, $res->getContent() );	
					}

					$issuesFilteredByDate = array();

					foreach ( $issues as $issue ) {
						$closed_at = strtotime( $issue['closed_at'] );
						if( $closed_at >= $dateLimit ){
							$issuesFilteredByDate[] = $issue;
						}
					}

					c::view()->issues = $issuesFilteredByDate;
				}

				c::view()->display('issues/search');


				break;
			case 'auth':
				$redir = 'http://'.$_SERVER['HTTP_HOST'].'admin/issues/authcallback';
				header('Location: https://github.com/login/oauth/authorize?client_id='.c::config()->github->id.'&scope=user,repo');
				exit;

				break;
				
			case 'authcallback':
				$code = $_REQUEST['code'];
				if ($code) {
					$data = ['client_id' => c::config()->github->id, 'client_secret' => c::config()->github->secret, 'code' => $code];
					$r = new Cana_Curl('https://github.com/login/oauth/access_token', $data);
					parse_str($r->output, $params);

					if (!$params['error']) {
						$token = $params['access_token'];
						setcookie('github-token', $token);
						header('Location: /issues');
						exit;
					} else {
						header('Location: /issues');
						exit;
					}
					exit;
				} else {
					header('Location: /issues');
					exit;
				}
				break;
			case 'logout':
				setcookie('github-token','');
				header('Location: /issues');
				exit;
				break;

			default:
				if (!$_COOKIE['github-token']) {
					c::view()->display('issues/auth');
				} else {
					header('Location: /issues/view');
					exit;
				}
				break;
		}

	}
}

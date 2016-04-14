<?php

class Cockpit_Github extends Cana_Model {

	const REPO = 'crunchbutton';
	const USER = 'crunchbutton';

	public static function commits($user, $repo, $branch = 'master') {
		return c::github()->api('repo')->commits()->all($user, $repo, ['sha' => $branch]);
	}

	public static function createTag($user, $repo, $tag, $sha = '', $body = '') {
		return true;
		return c::github()->api('repo')->releases()->create($user, $repo, [
			'tag_name' => $tag,
			'target_commitish' => $sha,
			'body' => $body
		]);
	}

	public static function createIssue( $title, $body, $assignee = null, $labels = null ){
		$params = ['title' => $title, 'body' => $body];
		if($assignee){
			$params['assignee'] = $assignee;
		}
		if($labels){
			if(!is_array($labels)){
				$labels = [$labels];
			}
			$params['labels'] = $labels;
		}
		return c::github()->api('issue')->create(self::USER, self::REPO, $params);
	}

	public static function tags($user, $repo) {
		$response = c::github()->getHttpClient()->get('repos/'.$user.'/'.$repo.'/git/refs/tags');
		$tags = Github\HttpClient\Message\ResponseMediator::getContent($response);
		return $tags;
	}
}
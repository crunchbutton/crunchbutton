<?php
	
class Cockpit_Github extends Cana_Model {
	public static function commits($user, $repo) {
		return c::github()->api('repo')->commits()->all($user, $repo, ['sha' => 'master']);
	}
	
	public static function createTag($user, $repo, $tag, $sha = '', $body = '') {
		return true;
		return c::github()->api('repo')->releases()->create($user, $repo, [
			'tag_name' => $tag,
			'target_commitish' => $sha,
			'body' => $body
		]);
	}
	
	public static function tags($user, $repo) {
		$response = c::github()->getHttpClient()->get('repos/'.$user.'/'.$repo.'/git/refs/tags');
		$tags     = Github\HttpClient\Message\ResponseMediator::getContent($response);
		return $tags;
	}
}
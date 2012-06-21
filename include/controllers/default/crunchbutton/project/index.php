<?php

class Controller_project extends Cana_Controller {
	public function init() {
		$id = Cana::getPagePiece(1);

		$project = Project::o($id);
		if ($project->id_project) {
			header('Location: /project/'.$project->permalink);
			exit;
		}
		
		if (strpos($id,'.json')) {
			$json = true;
			$id = str_replace('.json','',$id);
		}

		$project = Project::permalink($id);
		if (!$project->id_project) {
			header('Location: /projects');
		}
		
		if ($_REQUEST['source']) {
			print_r($project->source()); exit;
		}
		
		$facebook = new Cana_Facebook([
			'appId'	=> Cana::config()->facebook->app,
			'secret' => Cana::config()->facebook->secret,
		]);
		
		$feed = $facebook->api('/ExtraCredits/feed');

		foreach ($feed['data'] as $item) {
			//if ($item['from']['id'] == '87703622182')
			$feedItems[] = $item;
		}
		

		Cana::view()->feed = $feedItems;		
		Cana::view()->project = $project;
		Cana::view()->display('project/index');
	}
}
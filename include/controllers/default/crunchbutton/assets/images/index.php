<?php

class Controller_assets extends Cana_Controller {   
	public function init() {
		$page = Cana::app()->pages();
		if ($page[1] == 'images') {

			$page[4] = explode('.',$page[4]);
			array_pop($page[4]);
			$page[4] = implode('.',$page[4]);
			
			$path = Cana::config()->dirs->www.'assets/images/';

			$exts = ['jpg','jpeg','png','gif'];
			foreach ($exts as $ext) {
				$im = $page[2].'/'.$page[4].'.'.$ext;

				if (file_exists($path.$im)) {
					$file = $page[2].'/'.$page[4].'.'.$ext;
					break;
				}
			}

			$page[3] = explode('x',$page[3]);
			$params['height'] = $page[3][1];
			$params['width'] = $page[3][0];
			$params['crop'] = 1;
			$params['gravity'] = 'center';
			$params['format'] = $page[4][1];
			if ($params['format'] != 'jpg' && $params['format'] != 'png') {
				$params['format'] = 'jpg';
			}

			$params['img']			= $file;
			$params['cache'] 		= Cana::config()->dirs->pubcache.'images/';
			$params['path'] 		= $path;

			$thumb = new Cana_Thumb($params);
			$url = '/cache/images/'.$thumb->getFileName();
			header('Location: '.$url);
			//$thumb->displayThumb();
			exit;	
		}
	}
}
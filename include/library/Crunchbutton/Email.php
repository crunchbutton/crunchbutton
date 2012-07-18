<?php

class Crunchbutton_Email extends Cana_Email {

	public function buildView($params) {

		$params['theme'][] = 'mail/'.c::config()->defaults->theme.'/';
		$params['layout'] =  c::config()->defaults->layout;
		$params['base'] = c::config()->dirs->view;
		$view = new Cana_View($params);
		$this->view($view);
	}

	public function send() {
		return parent::send();
	}
	
	public function message() {
		return $this->mHtml;
	}

}
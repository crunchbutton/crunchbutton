<?php


class Crunchbutton_Email_CSDigest extends Email{
    private $_mailConfig;

    public function __construct($params) {
        $params['subject'] 		= 'Driver Feedback';
        $params['from'] 			= 'Crunchbutton <_USERNAME_>';
        $params['reply']			= 'Crunchbutton <_USERNAME_>';

        $this->buildView($params);
        //print_r($params['messages']);

        $params['messageHtml'] = $this->view()->render('cs/digest',['display' => true, 'set' =>
            ['messages' => $params['messages'],

                ]]);
        var_dump($params['messageHtml']);


        //parent::__construct($params);//sends emails
    }
}
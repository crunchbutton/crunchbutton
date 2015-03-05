<?php


class Crunchbutton_Cron_Job_CSTicketsDigest extends Crunchbutton_Cron_Log {

    public function run($params){

        //Crunchbutton_Newusers::sendEmailCLI();

        $supports = Crunchbutton_Support::q('select * from support ORDER BY id_support desc LIMIT 10');
        $params = array('users' => Crunchbutton_Support::getUsers(), 'messages'=> $supports);
        $some_var = new Crunchbutton_Email_CSDigest($params);
//var_dump($supports);
        // it always must call finished method at the end
        $this->finished();
    }
}
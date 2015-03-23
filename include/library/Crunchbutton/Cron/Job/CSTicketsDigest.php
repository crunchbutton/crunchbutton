<?php


class Crunchbutton_Cron_Job_CSTicketsDigest extends Crunchbutton_Cron_Log {

    public function run($params){

        //Crunchbutton_Newusers::sendEmailCLI();

        $supports = Crunchbutton_Support::q('SELECT
            support.*
            FROM support
            WHERE
            support.type != "WARNING"
            AND support.datetime > date_sub(now(), interval 1 day)
            ORDER BY support.id_support ASC
            limit 250');
        $params = array('users' => Crunchbutton_Support::getUsers(), 'messages'=> $supports);
        $some_var = new Crunchbutton_Email_CSDigest($params);
//var_dump($supports);
        // it always must call finished method at the end
        $this->finished();
    }
}
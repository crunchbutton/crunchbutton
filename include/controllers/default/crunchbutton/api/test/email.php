<?php
class Controller_api_test_email extends Crunchbutton_Support  {
public function init(){
    $some_var = new Crunchbutton_Cron_Job_CSTicketsDigest;
    $some_var->run();

    exit();
    }
}



<?php

class Crunchbutton_Support_Note extends Cana_Table {

  public function __construct($id = null) {
    parent::__construct();
    $this
      ->table('support_note')
      ->idVar('id_support_note')
      ->load($id);
  }

  public function notify() {
    self::notify_by_sms();
  }

  public function notify_by_sms() {
    $env = c::env() == 'live' ? 'live' : 'dev';
    $twilio = new Twilio(
        c::config()->twilio->{$env}->sid, 
        c::config()->twilio->{$env}->token);
    $support = $this->support();
    $phone = $this->support()->phone;
    if(!$phone) return;
    $rep_name = $support->rep()->name;
    $msg = ''
        .($rep_name ? "$rep_name: " : '')
        .$this->text;
    $msgs = str_split($msg, 160);
    foreach($msgs as $msg) {
      $twilio->account->sms_messages->create(
        c::config()->twilio->{$env}->outgoingTextCustomer,
        "+1$phone",
        $msg);
    }
  }

  public function save() {
    date_default_timezone_set('UTC'); // always save in utc
    $this->datetime = date('Y-m-d H:i:s e');
    parent::save();
  }

  public function support() {
    $note = Support::o($this->id_support);
    return $note;
  }

}

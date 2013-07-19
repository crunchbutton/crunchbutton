<?php

class Crunchbutton_Log extends Cana_Table {
	public static function __callStatic($func, $args) {
		$log = new Log;
		$log->level = $func;
		$log->type = $args[0]['type'];
		$log->data = json_encode($args[0]);
		$log->date = date('Y-m-d H:i:s');
		$log->save();
		
		if ($log->level == 'critical') {
			// send notifications
			
			$env = c::env() == 'live' ? 'live' : 'dev';
			$twilio = new Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
			
			foreach (c::config()->text as $supportName => $supportPhone) {
				$nums[] = $supportPhone;
			}
			
			/*
			$nums = array('_PHONE_');
			*/

			$b = $args[0]['action'] ? $args[0]['action'] : $log->data;
			$find = array(',"', '{', '}');
			$replace = array(",\n\t\"", "{\n\t", "\n}");
			$b = str_replace($find, $replace, $b);

			c::timeout(function() use ($nums, $b, $twilio, $env) {

				$message = str_split($b,160);

				foreach ($nums as $num) {
					foreach ($message as $msg) {
						$twilio->account->sms_messages->create(
							c::config()->twilio->{$env}->outgoingTextCustomer,
							'+1'.$num,
							$message
						);
					}
				}
			});
		}
	}
	

	public static function find($search = []) {
		$query = '
			select `log`.* from `log`
			where id_log is not null
		';

		if ($search['type']) {
			$query .= ' and `type`="'.$search['type'].'" ';
		}
		
		if ($search['level']) {
			$query .= ' and level="'.$search['level'].'" ';
		}

		if ($search['start']) {
			$s = new DateTime($search['start']);
			$query .= ' and DATE(`date`)>="'.$s->format('Y-m-d').'" ';
		}
		if ($search['end']) {
			$s = new DateTime($search['end']);
			$query .= ' and DATE(`date`)<="'.$s->format('Y-m-d').'" ';
		}

		if ($search['log']) {
			$query .= ' and `log`.id_log="'.$search['log'].'" ';
		}

		if ($search['search']) {
			$qn =  '';
			$q = '';
			$searches = explode(' ',$search['search']);
			foreach ($searches as $word) {
				if ($word{0} == '-') {
					$qn .= ' and `log`.data not like "%'.substr($word,1).'%" ';
				} else {
					$q .= '
						and (`log`.data like "%'.$word.'%")
					';
				}
			}
			$query .= $q.$qn;
		}

		$query .= '
			order by `date` DESC
		';

		if ($search['limit']) {
			$query .= ' limit '.$search['limit'].' ';
		}

		$logs = self::q($query);
		return $logs;
	}
	
	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
		}
		return $this->_date;
	}


	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('log')
			->idVar('id_log')
			->load($id);
	}
}
<?php

class Crunchbutton_Log extends Cana_Table {

	public static function __callStatic( $func, $args ) {

		$data = $args[0];

		$data['env'] = c::getEnv();

		$type = Log_Type::byType( $args[0]['type'] );

		$log = new Log;
		$log->level = $func;
		$log->id_log_type = $type->id_log_type;
		$log->type = $args[0]['type'];
		$log->data = json_encode( $data );
		$log->date = date('Y-m-d H:i:s');
		$log->save();

		if ($log->level == 'critical') {

			$info = json_decode( $log->data );
			$body = 'Critical error! Type: ' . $log->type . "\n";
			if( $info->id_order ){
				$id_order = $info->id_order;
			}
			if( $info->action ){
				$body .= $info->action;
			} else {
				$body = $log->data;
			}

			// Make these notifications pop up on support on cockpit #3008
			Crunchbutton_Support::createNewWarning( [ 'id_order' => $id_order, 'body' => $body ] );

			$b = $args[0]['action'] ? $args[0]['action'] : $log->data;
			$find = array(',"', '{', '}');
			$replace = array(",\n\t\"", "{\n\t", "\n}");
			$b = str_replace($find, $replace, $b);

			c::timeout(function() use ($nums, $b) {
				Crunchbutton_Message_Sms::send([
					'to' => Crunchbutton_Support::getUsers(),
					'message' => $b,
					'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT_WARNING
				]);
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
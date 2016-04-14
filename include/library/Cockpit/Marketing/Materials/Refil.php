<?php
class Cockpit_Marketing_Materials_Refil extends Cana_Table {

	const ASSIGNEE = 'elajohn';
	const LABEL = 'waffle: awaiting JEla';

	public function __construct($id = null){
		parent::__construct();
		$this
			->table('marketing_materials_refil')
			->idVar('id_marketing_materials_refil')
			->load($id);
	}

	public static function sendToGitHub(){
		$requests = self::q('SELECT
													mmr.id_marketing_materials_refil,
													mmr.id_admin,
													a.name,
													a.phone,
													a.email,
													apt.address,
													mmr.date
												FROM marketing_materials_refil mmr
												INNER JOIN admin a ON mmr.id_admin = a.id_admin
												INNER JOIN admin_payment_type apt ON apt.id_admin = mmr.id_admin
												WHERE mmr.github IS NULL');
		$out = [];
		if($requests && count($requests)){
			foreach($requests as $request){
				$date = new DateTime( $request->date, new DateTimeZone( c::config()->timezone ) );
				$date->setTimeZone(new DateTimeZone( Crunchbutton_Community_Shift::CB_TIMEZONE ));
				$out[] = '- [ ] ';
				$out[] = $request->name . ' - ' . Phone::formatted($request->phone) . "\n";
				$out[] = ' ' . $request->address . "\n";
				if($request->email){
					$out[] = ' ' . $request->email . "\n";
				}
				$out[] = ' ' . $date->format('M jS Y g:i:s A') . "\n";
				$out[] = "\n";
			}
			$body = join($out);
			if(trim($body)){
				$title = 'Marketing Materials Refill: ' . date('M jS Y');
				$issue = Github::createIssue( $title, $body, self::ASSIGNEE, self::LABEL);
				if($issue['number']){
					foreach($requests as $r){
						$request = self::o($r->id_marketing_materials_refil);
						$request->id_admin = $r->id_admin;
						$request->github = $issue['number'];
						$request->save();
					}
				}
			}
		}
	}
}
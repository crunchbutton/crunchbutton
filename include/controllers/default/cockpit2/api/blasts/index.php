<?php

class Controller_api_blasts extends Crunchbutton_Controller_RestAccount {
	public function init() {
		if (!c::admin()->permission()->check(['global', 'blast-all', 'blast-view' ])) {
			$this->error(401, true);
		}

		$blasts = Blast::q('
			select blast.*, count(*) users from blast
			left join blast_user using(id_blast)
			group by blast.id_blast
			order by blast.id_blast DESC
			limit 20
		');

		echo $blasts->json();
		exit;
	}
}
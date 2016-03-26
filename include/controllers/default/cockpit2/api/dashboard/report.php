<?php

class Controller_api_dashboard_report extends Crunchbutton_Controller_RestAccount {
	public function init() {
		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			$this->error(401, true);
		}
		$results = [];
		$queries = Crunchbutton_Report::q('select * from report where active = true');
		foreach ($queries as $query) {
			$r = c::db()->query($query->content);
			$result = [];
			while ($o = $r->fetch()) {
				$result[] = $o;
			}
			$results[$query->title] = $result;
		}
		echo json_encode($results);
	}
}

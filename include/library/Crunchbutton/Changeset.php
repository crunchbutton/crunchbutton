<?php

class Colaby_Changeset extends Cana_Model {

	public static function save($object) {

		$objectType = get_class($object);
		$current = new $objectType($object->{$object->idVar()});

		$oldVals = array();
		$newVals = array();

		foreach ($current->properties() as $var => $val) {
			if (isset($object->$var)) {
				if ($object->$var != $current->$var) {
					$oldVals[$var] = $current->$var;
					$newVals[$var] = $object->$var;
				}
			}
		}

		$set = Cana_Table::fromTable($object->table().'_change_set', $object->idVar().'_change_set', $object->db());
		$set->strip();
		$set->timestamp = date('Y-m-d H:i:s');
		$set->id_app_login = Cana::auth()->login()->appLogin()->id_app_login;
		$set->{$object->idVar()} = $object->{$object->idVar()};

		if (count($oldVals)) {
			$set->save();

			foreach ($oldVals as $field => $oldVal) {
				$change = Cana_Table::fromTable($object->table().'_change', $object->{$object->idVar()}.'_change', $object->db());
				$change->strip();
				$change->{$set->idVar()} = $set->{$set->idVar()};
				$change->field = $field;
				$change->new_value = $newVals[$field];
				$change->old_value = $oldVals[$field];
				$change->save();
			}
		}

		return $set;
	}
}
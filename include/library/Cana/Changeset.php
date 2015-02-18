<?php

class Cana_Changeset extends Cana_Model {

	public static function save($object, $options = array()) {

		$objectType = get_class($object);
		Cana::config()->cache->object = false;
		$current = new $objectType($object->{$object->idVar()});
		Cana::config()->cache->object = true;

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

		if (isset($options['custom'])) {
			foreach ($options['custom'] as $key => $customOption) {
				$oldVals[$key] = $customOption['old'];
				$newVals[$key] = $customOption['new'];
			}
		}

		$time = isset($options['timestamp']) ? $options['timestamp'] : date('Y-m-d H:i:s');

		// set
		$set = Cana_Table::fromTable((isset($options['set']['table']) ? $options['set']['table'] : $object->table().'_change_set'), (isset($options['set']['id']) ? $options['set']['id'] : $object->idVar().'_change_set'), $object->db());
		$set->strip();
		$set->timestamp = $time;

		if (isset($options['author_id'])) {
			$authorVar = $options['author_id'];
		} elseif (c::admin()->id_admin) {
			$authorVar = 'id_admin';
		} elseif (c::user()->id_user) {
			$authorVar = 'id_user';
		} elseif (isset($options['id_admin'])) {
			$authorVar = 'id_admin';
		}
			
		if ($authorVar) {
			if (isset($options['author'])) {
				$author = $options['author'];
			} elseif (c::admin()->id_admin) {
				$author = c::admin()->id_admin;
			} elseif (c::user()->id_user) {
				$author = c::user()->id_user;
				// there is some case where the change is made by a cron task
			} elseif (isset($options['id_admin']) ){
				$author = $options['id_admin'];
			}
			
			if ($author) {
				$set->{$authorVar} = $author;
			}
		}
		

		$set->{$object->idVar()} = $object->{$object->idVar()};

		// changes. only save set if theres at least one change
		if (count($oldVals)) {
			$set->save();

			foreach ($oldVals as $field => $oldVal) {
				$change = Cana_Table::fromTable((isset($options['change']['table']) ? $options['change']['table'] : $object->table().'_change'), (isset($options['change']['id']) ? $options['change']['id'] : $object->{$object->idVar()}.'_change'), $object->db());
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
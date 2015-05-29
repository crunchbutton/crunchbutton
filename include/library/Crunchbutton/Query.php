<?php

// query functions until i build a real query buider for cana/tipsy

class Crunchbutton_Query extends Cana_Model {
	public static function search($params) {

		$search = $params['search'];
		$q = '';

		if ($search) {
			if ($params['stripslashes']) {
				$search  = stripslashes($search);
			}
			
			$words = preg_split("/[\s,]*\\\"([^\\\"]+)\\\"[\s,]*|" . "[\s,]*'([^']+)'[\s,]*|" . "[\s,]+/", $search, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			$sq = '';

			foreach ($words as $k => $word) {
				$sq .= ($sq ? ' AND ' : '').'(';
				
				if ($word{0} == '-') {
					$word = substr($word, 1);
					$match = false;
				} else {
					$match = true;
				}
				
				if (!$word) {
					continue;
				}
				
				foreach ($params['fields'] as $key => $type) {
					$w = null;
					$sqq .= $sqq ? ' '.($match ? 'OR' : 'AND').' ' : '';

					if ($type == 'like' || $type == 'liker' || $type == 'likel') {
						$sqq .= ' '.$key.' '.($match ? '' : 'NOT').' LIKE ? ';
						$w = ($type == 'like' || $type == 'likel' ? '%' : '').$word.($type == 'like' || $type == 'liker' ? '%' : '');
					} elseif ($type == 'eq') {
						$sqq .= ' '.$key.' '.($match ? '' : '!').'= ? ';
					} elseif ($type == 'inteq') {
						$sqq .= ' '.$key.' '.($match ? '' : '!').'= ? ';
						$word = intval($word);
					} elseif ($type == 'gt') {
						$sqq .= ' '.$key.' > ? ';
					} elseif ($type == 'lt') {
						$sqq .= ' '.$key.' < ? ';
					}
					$sqq .= "\n";
					$keys[] = $w ? $w : $word;
				}

				$sq .= $sqq.')';
				$sqq = '';
			}
			$q .= '
				AND ('.$sq.')
			';
			$sq = '';
		}

		return ['query' => $q, 'keys' => $keys];
	}
}
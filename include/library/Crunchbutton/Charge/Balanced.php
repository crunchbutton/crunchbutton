<?php

class Crunchbutton_Charge_Balanced extends Cana_Model {
	public function __construct($params = []) {
		if ($params['card_id']) {
			$this->_card = Crunchbutton_Balanced_Card::byId($params['card_id']);
		}
	}

	public function charge($params = []) {

		$success = false;
		$reason = false;

		// if there is any card information provided, charge with it
		if ($params['card']) {
			$reason = true;
			try {
				$this->_card = Crunchbutton_Balanced_Card::byId($params['card']['id']);
				$c = $this->_card->debits->create([
					'amount' => $params['amount'] * 100,
					'appears_on_statement_as' => 'Crunchbutton',
					'description' => $params['restaurant']->name,
					'statement_descriptor' => $params['restaurant']->statementName()
				]);

			} catch (Exception $e) {
				Log::debug( [ 'card error' => 'balanced', 'Exception' => $e->description, 'type' => 'card-error' ]);
				$errors[] = 'Your card was declined. Please try again!';
				// $e->description
				$success = false;
			}
			if ($c->id) {
				$success = true;
				$txn = $c->id;
			}
		}

		// if there was no number, and there was a user with a stored card, use the users stored card
		if (!$params['card'] && $params['user'] && $this->card()->id) {

			$reason = true;
			try {
				$c = $this->card()->debits->create([
					'amount' => $params['amount'] * 100,
					'appears_on_statement_as' => 'Crunchbutton',
					'description' => $params['restaurant']->name
				]);

			} catch (Exception $e) {
				Log::debug( [ 'card error' => 'balanced', 'Exception' => $e->description, 'type' => 'card-error' ]);
				$errors[] = 'Your card was declined. Please try again!';
				$success = false;
			}

			if ($c->id) {
				$success = true;
				$txn = $c->id;
			}

		}

		if (!$reason && $params['user'] && $params['user']->stripe_id) {
			$reason = true;
			$errors[] = 'Please update your credit card information.';
		}

		if (!$reason) {
			$errors[] = 'Not enough card information.';
		}

		return [
			'status' => $success,
			'txn' => $txn,
			'errors' => $errors,
			'card' => $this->card()
		];
	}

	public function card() {
		return $this->_card;
	}
}
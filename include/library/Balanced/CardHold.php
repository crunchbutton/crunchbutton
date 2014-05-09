<?php

namespace Balanced;

use Balanced\Resource;
use \RESTful\URISpec;

/**
 * Represents pending debit of funds for an account. The funds for that debit
 * are held by the processor. You can later capture the hold, which results in
 * debit, or void it, which releases the held funds.
 *
 * Note that a hold can expire so you should always check
 * Balanced\Hold::expires_at.
 *
 * You create these using \Balanced\Account::hold.
 *
 * <code>
 * $marketplace = \Balanced\Marketplace::mine();
 *
 * $hold = $marketplace->holds->create(array(
 *   "amount" => 5000,
 *   "description" => "Some descriptive text for the debit in the dashboard",
 *   "source_uri" => "/v1/marketplaces/TEST-MP5FKPQwyjvVgTDt7EiRw3Kq/cards/CC15RAm6JJIEIae6bicvlWRw"
 * ));
 * </code>
 */
class CardHold extends Resource
{
    protected static $_uri_spec = null;

    public static function init()
    {
        self::$_uri_spec = new URISpec('card_holds', 'id', '/');
        self::$_registry->add(get_called_class());
    }

    /**
     ** Voids a pending hold. This releases the held funds. Once voided a hold
     * is no longer pending and cannot be re-captured or re-voided.
     *
     * @return \Balanced\Hold
     */
    public function void()
    {
        $this->unstore();

        return $this;
    }

    /**
     * Captures a pending hold. This results in a debit. Once captured a hold
     * is not longer pending can cannot be re-captured or re-voided.
     *
     * <code>
     * $debit = $hold->capture();
     *
     * @param int amount Optional Portion of the pending hold to capture. If not specified the full amount associated with the hold is captured.
     *
     * @return \Balanced\Debit
     */
    public function capture($amount = null)
    {
        return $this->debit = $this->debits->create(array(
            'amount' => $amount
        ));
    }
}

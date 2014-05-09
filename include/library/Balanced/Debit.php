<?php

namespace Balanced;

use Balanced\Resource;
use \RESTful\URISpec;

/**
 * Represents an account debit transaction.
 *
 * <code>
 * $card->debit(
 *    "5000",
 *    "Statement text",
 *    null,
 *    "Some descriptive text for the debit in the dashboard"
 * );
 * </code>
 */
class Debit extends Resource
{
    protected static $_uri_spec = null;

    public static function init()
    {
        self::$_uri_spec = new URISpec('debits', 'id', '/');
        self::$_registry->add(get_called_class());
    }


    /**
     * Debit an untokenized card
     *
     * @param int amount Amount to credit in USD pennies.
     * @param mixed card Associative array describing a card to debit. The card will *not* be stored.
     * @param string description Optional description of the credit.
     *
     * @return \Balanced\Debit
     *
     */
    public static function Card(
        $amount,
        $card,
        $description = null)
    {
        $debit = new Debit(array(
            'amount' => $amount,
            'source' => $card,
            'description' => $description
        ));
        $debit->save();
        return $debit;
    }


    /**
     * Create a refund for this debit. You can create multiple refunds for a
     * debit but the total amount of the refunds must be less than the debit
     * amount.
     *
     * @param int amount Optional amount of the refund in USD pennies. If unspecified then the full debit amount is used.
     * @param string description Optional description of the refund.
     * @param array[string]string meta Optional metadata to associate with the refund.
     *
     * @return \Balanced\Refund
     */
    public function refund(
        $amount = null,
        $description = null,
        $meta = null)
    {
        return $this->refunds->create(array(
            'amount' => $amount,
            'description' => $description,
            'meta' => $meta
            ));
    }
}

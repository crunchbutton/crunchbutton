<?php

namespace Balanced;

use Balanced\Resource;
use \RESTful\URISpec;

/**
 * Represents an account credit transaction.
 *
 * You create these using Balanced\Account::credit.
 *
 * <code>
 * $customer = Balanced\Customer::get('CUSTOMER_URI');
 * $customer->credit(100);
 * </code>
 */
class Credit extends Resource
{
    protected static $_uri_spec = null;

    public static function init()
    {
        self::$_uri_spec = new URISpec('credits', 'id', '/');
        self::$_registry->add(get_called_class());
    }

    /**
     * Credit an unstored bank account.
     *
     * @param int amount Amount to credit in USD pennies.
     * @param mixed bank_account Associative array describing a bank account to credit. The bank account will *not* be stored.
     * @param string description Optional description of the credit.
     *
     * @return \Balanced\Credit
     *
     * <code>
     * $bank_account_info = array(
     *   "account_number" => "9900000001",
     *   "name" => "Johann Bernoulli",
     *   "routing_number" => "121000358",
     *   "type" => "checking",
     * );
     * $credit = Balanced\Credit::bankAccount(
     *   10000,
     *   $bank_account_info
     * );
     * </code>
     */
    public static function bankAccount(
        $amount,
        $bank_account,
        $description = null)
    {
        $credit = new Credit(array(
           'amount' => $amount,
           'destination' => $bank_account,
           'description' => $description
        ));
        $credit->save();
        return $credit;
    }

    public function reverse(
        $amount = null,
        $description = null,
        $meta = null)
    {
        return $this->reversals->create(array(
            'amount' => $amount,
            'description' => $description,
            'meta' => $meta
        ));
    }
}

<?php

namespace Balanced;

use Balanced\Resource;
use \RESTful\URISpec;

/**
 * Represents a bank account.
 *
 *
 * <code>
 * $bank_account = new \Balanced\BankAccount(array(
 *   "account_number" => "9900000001",
 *   "name" => "Johann Bernoulli",
 *   "routing_number" => "121000358",
 *   "type" => "checking",
 * ));
 * $bank_account->save();
 * </code>
 */
class BankAccount extends Resource
{
    protected static $_uri_spec = null;

    public static function init()
    {
        self::$_uri_spec = new URISpec('bank_accounts', 'id', '/');
        self::$_registry->add(get_called_class());
    }

    /**
     * Credit a bank account.
     *
     * @param int amount Amount to credit in USD pennies.
     * @param string description Optional description of the credit.
     * @param string appears_on_statement_as Optional description of the credit as it will appears on the customer's billing statement.
     *
     * @return \Balanced\Credit
     *
     * <code>
     * $bank_account = new \Balanced\BankAccount(array(
     *   "account_number" => "9900000001",
     *   "name" => "Johann Bernoulli",
     *   "routing_number" => "121000358",
     *   "type" => "checking",
     * ));
     *
     * $credit = $bank_account->credit(123, 'something descriptive');
     * </code>
     */
    public function credit(
            $amount,
            $description = null,
            $meta = null,
            $appears_on_statement_as = null,
            $order = null)
    {
        return $this->credits->create(array(
            'amount' => $amount,
            'description' => $description,
            'meta' => $meta,
            'appears_on_statement_as' => $appears_on_statement_as,
            'order' => $order
        ));

    }

    public function debit(
        $amount,
        $appears_on_statement_as = null,
        $description = null,
        $meta = null,
        $order = null)
    {
        return $this->debits->create(array(
            'amount' => $amount,
            'appears_on_statement_as' => $appears_on_statement_as,
            'description' => $description,
            'meta' => $meta,
            'order' => $order
        ));
    }

    public function associateToCustomer($customer) {
        if(is_string($customer)) {
            $this->links->customer = $customer;
        } else {
            $this->links->customer = $customer->href;
        }
        $this->save();
    }

    public function verify()
    {
        $response = self::getClient()->post(
            $this->bank_account_verifications->uri, array()
        );
        $verification = new BankAccountVerification();
        $verification->_objectify(
            $response->body->bank_account_verifications[0],
            $response->body->links);
        return $verification;
    }

    public function confirm($amount_1, $amount_2)
    {
        return $this->bank_account_verifications
            ->first()->confirm($amount_1, $amount_2);
    }

    public function invalidate()
    {
        return $this->unstore();
    }
}

/**
 * Represents an verification for a bank account which is a pre-requisite if
 * you want to create debits using the associated bank account. The side-effect
 * of creating a verification is that 2 random amounts will be deposited into
 * the account which must then be confirmed via the confirm method to ensure
 * that you have access to the bank account in question.
 *
 * You can create these via Balanced\Marketplace::bank_accounts::verify.
 *
 * <code>
 * $marketplace = \Balanced\Marketplace::mine();
 *
 * $bank_account = $marketplace->bank_accounts->create(array(
 *     'name' => 'name',
 *     'account_number' => '11223344',
 *     'bank_code' => '1313123',
 *     ));
 *
 * $verification = $bank_account->verify();
 * </code>
 */
class BankAccountVerification extends Resource {

    protected static $_uri_spec = null;

    public static function init()
    {
        self::$_uri_spec = new URISpec('verifications', 'id', '/', 'bank_account_verifications');
        self::$_registry->add(get_called_class());
    }

    public function confirm($amount1, $amount2) {
        $this->amount_1 = $amount1;
        $this->amount_2 = $amount2;
        $this->save();
        return $this;
    }
}

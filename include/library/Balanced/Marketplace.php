<?php

namespace Balanced;

use Balanced\Resource;
use Balanced\Errors;
use Balanced\Account;
use \RESTful\URISpec;

/**
 * Represents a marketplace.
 *
 * To get started you create an api key and then create a marketplace:
 *
 * <code>
 * $api_key = new \Balanced\APIKey();
 * $api_key->save();
 * $secret = $api_key->secret  // better save this somewhere
 * print $secret;
 * \Balanced\Settings::$api_key = $secret;
 *
 * $marketplace = new \Balanced\Marketplace();
 * $marketplace->save();
 * var_dump($marketplace);
 * </code>
 *
 * Each api key is uniquely associated with an api key so once you've created a
 * marketplace:
 *
 * <code>
 * \Balanced\Settings::$api_key = $secret;
 * $marketplace = \Balanced\Marketplace::mine();  // this is the marketplace associated with $secret
 * </code>
 */
class Marketplace extends Resource
{
    protected static $_uri_spec = null;

    public static function init()
    {
        self::$_uri_spec = new URISpec('marketplaces', 'id', '/');
        self::$_registry->add(get_called_class());
    }

    /**
     * Get the marketplace associated with the currently configured
     * \Balanced\Settings::$api_key.
     *
     * @throws \RESTful\Exceptions\NoResultFound
     * @return \Balanced\Marketplace
     */
    public static function mine()
    {
        return self::query()->one();
    }

    /**
     * Create a card. These can later be associated with an account using
     * \Balanced\Account->addCard or \Balanced\Marketplace->createBuyer.
     *
     * @param string street_address Street address. Use null if there is no address for the card.
     * @param string city City. Use null if there is no address for the card.
     * @param string state State.  Use null if there is no address for the card.
     * @param string postal_code Postal code. Use null if there is no address for the card.
     * @param string name Name as it appears on the card.
     * @param string card_number Card number.
     * @param string security_code Card security code. Use null if it is no available.
     * @param int expiration_month Expiration month.
     * @param int expiration_year Expiration year.
     *
     * @return \Balanced\Card
     */
    public function createCard(
        $street_address,
        $city,
        $state,
        $postal_code,
        $name,
        $card_number,
        $security_code,
        $expiration_month,
        $expiration_year)
    {
        return $this->cards->create(array(
            'address' => array(
                'street_address' => $street_address,
                'city' => $city,
                'state' => $state,
                'postal_code' => $postal_code,
            ),
            'name' => $name,
            'number' => $card_number,
            'cvv' => $security_code,
            'expiration_month' => $expiration_month,
            'expiration_year' => $expiration_year
            ));
    }

    /**
     * Create a bank account. These can later be associated with an account
     * using \Balanced\Account->addBankAccount.
     *
     * @param string name Name of the account holder.
     * @param string account_number Account number.
     * @param string routing_number Bank code or routing number.
     * @param string type checking or savings
     * @param array meta Single level mapping from string keys to string values.
     *
     * @return \Balanced\BankAccount
     */
    public function createBankAccount(
        $name,
        $account_number,
        $routing_number,
        $type,
        $meta = null
        )
    {
        return $this->bank_accounts->create(array(
            'name'           => $name,
            'account_number' => $account_number,
            'routing_number' => $routing_number,
            'type'           => $type,
            'meta'           => $meta
            ));
    }

    /**
     * Create a role-less account. You can later turn this into a buyer by
     * adding a funding source (e.g a card) or a merchant using
     * \Balanced\Account->promoteToMerchant.
     *
     * @param string email_address Optional email address. There can only be one account with this email address.
     * @param array[string]string meta Optional metadata to associate with the account.
     *
     * @return \Balanced\Account
     */
    public function createCustomer($email_address = null, $meta = null, $name = null)
    {
        return $this->customers->create(array(
            'email' => $email_address,
            'meta' => $meta,
            'name' => $name
            ));
    }

    /**
     * Find or create a role-less account by email address. You can later turn
     * this into a buyer by adding a funding source (e.g a card) or a merchant
     * using \Balanced\Account->promoteToMerchant.
     *
     * @param string email_address Email address. There can only be one account with this email address.
     *
     * @return \Balanced\Account
     */
    function findOrCreateCustomerByEmailAddress($email_address)
    {
    	$marketplace = Marketplace::mine();
        $customer = $marketplace->customers->query()
                               ->filter(Customer::$f->email->eq($email_address))
                               ->first();
        if($customer) {
            return $customer;
        }
        return $this->createCustomer($email_address);
    }

    /**
     * Create a buyer account.
     *
     * @param string email_address Optional email address. There can only be one account with this email address.
     * @param string card_href Href referencing a card to associate with the account.
     * @param array[string]string meta Optional metadata to associate with the account.
     * @param string name Optional name of the account.
     *
     * @return \Balanced\Customer
     */
    public function createBuyer(
        $email_address,
        $card_href,
        $meta = null,
        $name = null)
    {
        $customer = $this->customers->create(array(
            'email' => $email_address,
            'meta' => $meta,
            'name' => $name
            ));
        $card = Card::get($card_href);
        $card->associateToCustomer($customer);
        return $customer;
    }

    /**
     * Create a merchant account.
     *
     * Unlike buyers the identity of a merchant must be established before
     * the account can function as a merchant (i.e. be credited). A merchant
     * can be either a person or a business. Either way that information is
     * represented as an associative array and passed as the merchant parameter
     * when creating the merchant account.
     *
     * For a person the array looks like this:
     *
     * <code>
     * array(
     *     'type' => 'person',
     *     'name' => 'William James',
     *     'tax_id' => '393-48-3992',
     *     'street_address' => '167 West 74th Street',
     *     'postal_code' => '10023',
     *     'dob' => '1842-01-01',
     *     'phone_number' => '+16505551234',
     *     'country_code' => 'USA'
     *     )
     * </code>
     *
     * For a business the array looks like this:
     *
     * <code>
     * array(
     *     'type' => 'business',
     *     'name' => 'Levain Bakery',
     *     'tax_id' => '253912384',
     *     'street_address' => '167 West 74th Street',
     *     'postal_code' => '10023',
     *     'phone_number' => '+16505551234',
     *     'country_code' => 'USA',
     *     'person' => array(
     *         'name' => 'William James',
     *         'tax_id' => '393483992',
     *         'street_address' => '167 West 74th Street',
     *         'postal_code' => '10023',
     *         'dob' => '1842-01-01',
     *         'phone_number' => '+16505551234',
     *         'country_code' => 'USA',
     *         )
     *     )
     * </code>
     *
     * In some cases the identity of the merchant, person or business, cannot
     * be verified in which case a \Balanced\Exceptions\HTTPError is thrown:
     *
     * <code>
     * $identity = array(
     *     'type' => 'business',
     *     'name' => 'Levain Bakery',
     *     'tax_id' => '253912384',
     *     'street_address' => '167 West 74th Street',
     *     'postal_code' => '10023',
     *     'phone_number' => '+16505551234',
     *     'country_code' => 'USA',
     *     'person' => array(
     *         'name' => 'William James',
     *         'tax_id' => '393483992',
     *         'street_address' => '167 West 74th Street',
     *         'postal_code' => '10023',
     *         'dob' => '1842-01-01',
     *         'phone_number' => '+16505551234',
     *         'country_code' => 'USA',
     *         ),
     *     );
     *
     *  try {
     *      $merchant = \Balanced\Marketplace::mine()->createMerchant(
     *          'merchant@example.com',
     *          $identity,
     *          );
     *  catch (\Balanced\Exceptions\HTTPError $e) {
     *      if ($e->code != 300) {
     *          throw $e;
     *      }
     *      print e->response->header['Location'] // this is where merchant must signup
     *  }
     * </code>
     *
     * Once the merchant has completed signup you can use the resulting URI to
     * create an account for them on your marketplace:
     *
     * <code>
     * $merchant = self::$marketplace->createMerchant(
     *     'merchant@example.com',
     *     null,
     *     null,
     *     $merchant_uri
     *     );
     * </coe>
     *
     * @param string email_address Optional email address. There can only be one account with this email address.
     * @param array[string]mixed merchant Associative array describing the merchants identity.
     * @param string $bank_account_uri Optional URI referencing a bank account to associate with this account.
     * @param string $merchant_uri URI of a merchant created via the redirection sign-up flow.
     * @param string $name Optional name of the merchant.
     * @param array[string]string meta Optional metadata to associate with the account.
     *
     * @return \Balanced\Account
     */
    public function createMerchant(
        $email_address = null,
        $merchant = null,
        $bank_account_href = null,
        $name = null,
        $meta = null)
    {
        $params = array(
            'email_address' => $email_address,
            'name' => $name,
            'meta' => $meta,
        );
        if($merchant) {
            $params = array_merge($params, $merchant);
        }
        $customer = $this->customers->create();
        if($bank_account_href) {
            $bank_account = BankAccount::get($bank_account_href);
            $bank_account->associateToCustomer($customer);
        }
        return $customer;
    }

    /*
     * Create a callback.
     *
     * @param string url URL of callback.
     */
    public function createCallback(
        $url
    )
    {
        return $this->callbacks->create(array(
            'url' => $url
        ));
    }
}

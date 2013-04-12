<?php
namespace AktiveMerchant\Billing\Gateways;

use AktiveMerchant\Billing\Gateway;
use AktiveMerchant\Billing\Gateways\Skrill\SkrillInitialResponse;
use AktiveMerchant\Billing\Gateways\Skrill\SkrillResponse;
/**
 * Description of Skrill/Moneybookers
 *
 * @package Aktive-Merchant
 * @author  mlapko<maxlapko@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class Skrill extends Gateway
{
    const TEST_URL = 'https://www.moneybookers.com/app/payment.pl';
    const LIVE_URL = 'https://www.moneybookers.com/app/payment.pl';

    public static $money_format = 'dollars';
    # The countries the gateway supports merchants from as 2 digit ISO country codes

    public static $supported_countries = array('US');

    # The card types supported by the payment gateway
    public static $supported_cardtypes = array('visa', 'master', 'american_express', 'switch', 'solo', 'maestro');

    # The homepage URL of the gateway
    public static $homepage_url = 'https://www.moneybookers.com/app/payment.pl';

    # The display name of the gateway
    public static $display_name = 'Moneybookers Payment Gateway';
    public static $default_currency = 'USD';
    public static $default_language = 'EN';


    private $options = array();
    private $version = '6.8';
    private $post = array();

    /**
     * $options array includes login parameters of merchant and optional currency.
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->required_options('login, secret_word', $options);

        if (isset($options['currency'])) {
            self::$default_currency = $options['currency'];            
        }
        if (isset($options['version'])) {
            $this->version = $options['version'];
        }

        $this->options = $options;
    }
    
    /**
     * Prepare purchase
     * 
     * @param number $money
     * @param array $options
     *
     * @return AktiveMerchant\Billing\Gateways\Skrill\SkrillInitialResponse
     */
    public function setupPurchase($money, $options = array())
    {        
        $this->required_options('return_url, cancel_url, status_url, detail1_description, detail1_text', $options);

        $params = array(
            'return_url'          => $options['return_url'],
            'return_url_text'     => isset($options['return_url_text']) ? $options['return_url_text'] : '',
            'cancel_url'          => $options['cancel_url'],
            'detail1_description' => $options['detail1_description'],
            'detail1_text'        => $options['detail1_text'],
            'amount'              => $this->amount($money),
            'language'            => isset($options['language']) ? $options['language'] : self::$default_language,
            'status_url'          => $options['status_url'],
            'status_url2'         => isset($options['status_url2']) ? $options['status_url2'] : '',
            'currency'            => self::$default_currency,
            'prepare_only'        => 1
        );

        $this->post = array_merge($this->post, $params, $this->get_optional_params($options));

        return $this->_commit();
    }
    
    /**
     * Response for IPN
     * @param type $params
     * @return \AktiveMerchant\Billing\Gateways\Skrill\SkrillResponse
     */
    public function createResponse($params = array())
    {
        $params = empty($params) ? $_REQUEST : $params;
        return new SkrillResponse($this, $params);
    }
    
    /**
     *
     * @return array 
     */
    public function getOptions()
    {
        return $this->options;
    }
    
    /**
     *
     * @param string $token
     *
     * @return string url address to redirect
     */
    public function urlForToken($token)
    {
        $redirectUrl = $this->isTest() ? self::TEST_URL : self::LIVE_URL;
        return $redirectUrl . '?sid=' . $token;
    }
    
    protected function _postData()
    {
        $params = array(            
            'pay_to_email' => $this->options['login'],
            'currency'     => self::$default_currency
        );

        $this->post = array_merge($this->post, $params);
        return $this->urlize($this->post);
    }
    
    /**
     *
     * @param string $action
     * @param number $money
     * @param array  $parameters
     *
     * @return \AktiveMerchant\Billing\Gateways\Skrill\SkrillInitialResponse
     */
    private function _commit()
    {
        $url = $this->isTest() ? self::TEST_URL : self::LIVE_URL;

        $response = $this->ssl_post($url, $this->_postData());

        return new SkrillInitialResponse($response);
    }    
    
    private function get_optional_params($options)
    {
        $params = array();

        if (isset($options['billing_address'])) {
            $billing = $options['billing_address'];
            $params = array(
                'pay_from_email'        => $billing['email'],
                'title'                 => $billing['title'],
                'firstname'             => $billing['firstname'],
                'lastname'              => $billing['lastname'],
                'date_of_birth'         => $billing['date_of_birth'],
                'address'               => $billing['address'],
                'address2'              => $billing['address2'],
                'phone_number'          => $billing['phone_number'],
                'postal_code'           => $billing['postal_code'],
                'city'                  => $billing['city'],
                'state'                 => $billing['state'],
                'country'               => $billing['country'],
            );
        }      

        # Merchant may specify a detailed calculation for the total
        # amount payable. Please note that moneybookers does not check
        # the validity of these data - they are only displayed in the
        # details section of Step 2 of the payment process.
        $optional = array(
            'amount2_description',  # e.g. "Product Price:"
            'amount2',              # e.g. "29.90"
            'amount3_description',  # e.g. "Handling Fees:"
            'amount3',              # e.g. "3.10"
            'amount4_description',  # e.g. "VAT (20%):"
            'amount4',              # e.g. "6.60"

            # customer
            'dynamic_descriptor',   # merchant name
            'confirmation_note',    # thank you note
            'merchant_fields',      # fields which will be sent back to you

            # product details (up to 5 fields)
            # Merchant may show up to 5 details about the product or
            # transfer in the 'Payment Details' section of Step 2 of the
            # process. The detail1_descritpion is shown on the left side.
            'detail2_description',
            'detail2_text',
            'detail3_description',
            'detail3_text',
            'detail4_description',
            'detail4_text',
            'detail5_description',
            'detail5_text',

            # recurring billing
            'rec_period',
            'rec_grace_period',
            'rec_cycle',
            'ondemand_max_currency',
            'transaction_id'
        );
        if (isset($options['merchant_fields'])) {
            $optional = array_merge($optional, preg_split('/[\s,]+/', $options['merchant_fields'], -1, PREG_SPLIT_NO_EMPTY));  
        }
        foreach ($optional as $value) {
            if (isset($options[$value])) {
                $params[$value] = $options[$value];
            }
        }
        return $params;
    }

}
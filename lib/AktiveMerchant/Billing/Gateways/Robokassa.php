<?php
namespace AktiveMerchant\Billing\Gateways;

use AktiveMerchant\Billing\Gateway;
use AktiveMerchant\Billing\Gateways\Robokassa\RobokassaResponse;
use AktiveMerchant\Billing\Gateways\Robokassa\RobokassaDummyResponse;
/**
 * Description of Robokassa
 *
 * @package Aktive-Merchant
 * @author  mlapko<maxlapko@gmail.com>
 * 
 * @description http://robokassa.ru/ru/Doc/Ru/Interface.aspx
 * 
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class Robokassa extends Gateway
{
    const TEST_URL = 'http://test.robokassa.ru/Index.aspx';
    const LIVE_URL = 'https://merchant.roboxchange.com/index.aspx';
    const PARAM_PREFIX = 'Shp_';
    
    # The countries the gateway supports merchants from as 2 digit ISO country codes
    public static $money_format = 'dollars';
    public static $supported_countries = array('RU');

    # The homepage URL of the gateway
    public static $homepage_url = 'http://robokassa.ru/';

    # The display name of the gateway
    public static $display_name = 'Robokassa Gateway';
    public static $default_currency = 'RUB';
    public static $default_encoding = 'utf-8';


    private $_options;
    private $_post = array();
    
    private $_params = array();
    
    /**
     * $options array includes login parameters of merchant and optional currency.
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
        $this->required_options('login, password1, password2', $options);
        $this->_options = $options;
    }
    
    /**
     *
     * @param number $money
     * @param array $options
     * 
     * @return string
     */
    public function setupPurchase($money, $options = array())
    {        
        $this->_params = array();
        $this->required_options('InvId, Desc', $options);
        $this->_post = array(
            'MrchLogin' => $this->_options['login'],
            'OutSum'    => $this->amount($money),
            'InvId'     => $options['InvId'],
            'Desc'      => $options['Desc'],
        );
        $this->_post = array_merge($this->_post, $this->_getOptionalParams($options));
        $this->_post['SignatureValue'] = $this->_createRequestSignature();
        return new RobokassaDummyResponse();
    }
    
    /**
     *
     * @param type $params
     * @return AktiveMerchant\Billing\Gateways\Robokassa\RobokassaResponse 
     */
    public function createResultResponse($params = array())
    {
        $params = empty($params) ? $_REQUEST : $params;
        return new RobokassaResponse($this, $params, RobokassaResponse::RESULT);
    }
    
    /**
     *
     * @param array $params
     * 
     * @return AktiveMerchant\Billing\Gateways\Robokassa\RobokassaResponse 
     */
    public function createSuccessResponse($params = array())
    {
        $params = empty($params) ? $_REQUEST : $params;
        return new RobokassaResponse($this, $params, RobokassaResponse::SUCCESS);
    }
    
    /**
     *
     * @return array 
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
    /**    
     *
     * @return string url address to redirect
     */
    public function urlForToken($token = '')
    {
        $redirectUrl = $this->isTest() ? self::TEST_URL : self::LIVE_URL;
        return $redirectUrl . '?' . $this->urlize($this->_post);
    }    
    
    private function _getOptionalParams($options)
    {
        $params = array();           

        $optional = array('IncCurrLabel', 'Culture', 'Encoding');
        foreach ($optional as $value) {
            if (isset($options[$value])) {
                $params[$value] = $options[$value];
            }
        }
        if (isset($options['extra_params'])) {
            foreach ($options['extra_params'] as $key => $value) {
                $this->_params[] = self::PARAM_PREFIX . $key . '=' . $value;
                $params[self::PARAM_PREFIX . $key] = $value;                
            }
        }
        return $params;
    }
    
    /**
     * 
     * @return string 
     */
    protected function _createRequestSignature()
    {   
        $params = '';
        if (count($this->_params) > 0) {
            $params = ':' . implode(':', $this->_params);
        }
        return md5("{$this->_options['login']}:{$this->_post['OutSum']}:{$this->_post['InvId']}:{$this->_options['password1']}$params");
    }    

}
<?php
namespace AktiveMerchant\Billing\Gateways\Skrill;

class SkrillResponse 
{
    /**
     *
     * @var array 
     */
    private $_params;
    /**
     *
     * @var array
     */
    private $_options;
    
    private $_signature;
    
    public function __construct($gateway, $params)
    {
        $this->_params = $params;
        $this->_options = $gateway->getOptions();
        $this->_createSignature();       
    }
    
    /**
     * @return boolean 
     */
    public function success()
    {
        return $this->_signature == $this->_params['md5sig'] && $this->_params['status'] == 2;
    }
    
    /**
     * @return boolean 
     */
    public function pending()
    {
        return $this->_signature == $this->_params['md5sig'] && $this->_params['status'] == 0;
    }
    
    /**
     * @return boolean 
     */
    public function cancelled()
    {
        return $this->_signature == $this->_params['md5sig'] && $this->_params['status'] == -1;
    }
    
    /**
     * @return boolean 
     */
    public function failed()
    {
        return $this->_signature == $this->_params['md5sig'] && $this->_params['status'] == -2;
    }
    
    /**
     * @return boolean 
     */
    public function chargeback()
    {
        return $this->_signature == $this->_params['md5sig'] && $this->_params['status'] == -3;
    }    
    
    /**
     * @return float 
     */
    public function amount()
    {
        return $this->_params['amount'];
    }
    
    /**
     * @return string 
     */
    public function currency()
    {
        return $this->_params['currency'];
    }
    
    /**
     * failed_reason_code
     * @return string 
     */
    public function message()
    {
        return isset($this->_params['failed_reason_code']) ? $this->_params['failed_reason_code'] : '';
    }
    
    /**
     *  @return array
     */
    public function params() 
    {
       return $this->_params; 
    }
    
    public function __toString()
    {
        return print_r($this->_params, true);
    }    

    protected function _createSignature()
    {
        if ($this->_params['pay_to_email'] !== $this->_options['login']) {
            return;
        }
        $concatFields = $this->_params['merchant_id']
            . $this->_params['transaction_id']
            . strtoupper(md5($this->_options['secret_word']))
            . $this->_params['mb_amount']
            . $this->_params['mb_currency']
            . $this->_params['status'];
        $this->_signature = strtoupper(md5($concatFields));
    }
}

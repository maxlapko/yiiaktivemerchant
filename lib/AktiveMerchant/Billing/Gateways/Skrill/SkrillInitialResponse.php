<?php
namespace AktiveMerchant\Billing\Gateways\Skrill;

class SkrillInitialResponse
{
    private $_params;
    
    public function __construct($response)
    {
        $this->_params = trim($response);
    }    
    /**
     * @return string 
     */
    public function token()
    {        
        return $this->success() ? $this->_params : null;
    }
    
    /**
     * @return boolean 
     */
    public function success()
    {
        return preg_match('/^\w{32}$/', $this->_params);
    }
    
    public function params()
    {
        return $this->_params;
    }
}
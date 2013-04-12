<?php
namespace AktiveMerchant\Billing\Gateways\Robokassa;

class RobokassaDummyResponse
{
    /**
     * @return boolean 
     */
    public function success()
    {
        return true;
    }
    
    /**
     * @return string 
     */
    public function token()
    {
        return '';
    }   
    
    /**
     * @retrun array
     */
    public function params()
    {
        return array();
    }
    
    /**
     * @retrun string
     */
    public function message()
    {
        return '';
    }    
}
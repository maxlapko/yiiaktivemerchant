<?php
namespace AktiveMerchant\Billing\Gateways\Robokassa;

class RobokassaResponse
{
    protected $_params;    
    
    protected $_signature;
    
    protected $_extraParams = array();
    
    const RESULT = 'result';
    const SUCCESS = 'success';
    
    /**
     *
     * @param AktiveMerchant\Billing\Gateways\Robokassa $gateway
     * @param array $params 
     */
    public function __construct($gateway, $params, $type)
    {
        $this->_params = $params;
        $this->_validate();
        $this->_createSignature($gateway->getOptions(), $type);
    }
    
    /**
     * @return boolean 
     */
    public function success()
    {
        return $this->_signature === $this->_params['SignatureValue'];
    }
    
    /**
     * @retrun array
     */
    public function params()
    {
        return $this->_params;
    }
    
    /**
     * @retrun string
     */
    public function message()
    {
        return '';
    }

    /**
     * @retrun array
     */
    public function extraParams()
    {
        return $this->_extraParams;
    }
    
    /**
     * @retrun float
     */
    public function amount()
    {
        return $this->_params['OutSum'];
    }
    
    /**
     * @retrun string
     */
    public function invoice()
    {
        return $this->_params['InvId'];
    }
    
    
    
    protected function _createSignature($options, $type)
    { 
        $this->_params['SignatureValue'] = strtoupper($this->_params['SignatureValue']);
        $prefix = Merchant_Billing_Robokassa::PARAM_PREFIX;
        $params = '';
        foreach ($this->_params as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                $params .= ":$key=$value";
                $this->_extraParams[preg_replace('/^' . $prefix . '/', '', $key, 1)] = $value; 
            }
        }
        $password = $type === self::RESULT ? $options['password2'] : $options['password1'];
        $this->_signature = strtoupper(md5(
            "{$this->_params['OutSum']}:{$this->_params['InvId']}:$password$params"
        ));;
    }
    
    /**
     * validate required fields
     * @throws Exception id field is empty
     */
    private function _validate()
    {
        foreach (array('OutSum', 'InvId', 'SignatureValue') as $field) {
            if (empty($this->_params[$field])) {
                throw new Exception("The $field field is required for Robokassa response.");
            }            
        }
    }
}
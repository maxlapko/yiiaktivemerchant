<?php
/**
 * Description of ActiveMerchant
 *
 * @author mlapko
 */
class ActiveMerchant extends CApplicationComponent
{
    /**
     *
     * @var string test|live 
     */
    public $mode = 'test';
    
    public $gateways = array();
    protected $_gateways = array();
    
    public function init()
    {
        parent::init();
        self::register();
        AktiveMerchant\Billing\Base::mode($this->mode);
    }
    
    public static function register()
    {
        Yii::setPathOfAlias('AktiveMerchant', dirname(__FILE__) . '/lib/AktiveMerchant');
    }

    /**
     * Get supported gateway
     * 
     * @param type $name
     * @return type 
     */
    public function getGateway($name)
    {
        if (!isset($this->_gateways[$name])) {
            $this->_gateways[$name] = $this->_initGateway($name);
        }
        return $this->_gateways[$name];
    }    
    
    /**
     * @param array $params
     * 
     * @return AktiveMerchant\Billing\CreditCard
     */
    public function getCreditCard($params)
    {
        return new AktiveMerchant\Billing\CreditCard($params);
    }
    
    /**
     *
     * @param string $name
     * 
     * @return AktiveMerchant\Billing\Base 
     */
    protected function _initGateway($name)
    {
        if ($this->_existsGateway($name)) {
            return AktiveMerchant\Billing\Base::gateway($name, $this->gateways[$name]);
        }
        throw new Exception('Unsupport "' . $name . '" gateway.');
    }

    /**
     *
     * @param string $name
     * @return boolean 
     */
    protected function _existsGateway($name)
    {
        return isset($this->gateways[$name]);    
    }
    
}
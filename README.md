Wrapper for aktive merchant libary

source: https://github.com/akDeveloper/Aktive-Merchant

Requirements
- PHP 5.3.3+
- cUrl
- SimpleXML

```php
'components' => array(
    'payment' => array(
        'class' => 'ext.activemerchant.ActiveMerchant',
        'mode' => 'test', //live
        'gateways' => array(
            'PaypalExpress' => array(
                'login'     => 'blabla',
                'password'  => 'password',
                'signature' => '....',
                'currency'  => 'USD'
            ),
            'Paypal' => array(
                'login'     => 'blabla2',
                'password'  => 'password2',
                'signature' => '.....',
                'currency'  => 'USD'
            ),
        ),           
    ),
),

```

Usage

```php
$payment = Yii::app()->getComponent('payment');
$gateway->getGateway('Paypal');
$creaditCard = $payment->getCreditCard( 
    array(
        "first_name" => "John",
        "last_name" => "Doe",
        "number" => "41111111111111",
        "month" => "12",
        "year" => "2012",
        "verification_value" => "123"
    )
);

$creaditCard->isValid(); // Returns true or false

$options = array(
    'order_id' => 'REF' . $gateway->generateUniqueId(),
    'description' => 'Test Transaction',
    'address' => array(
        'address1' => '1234 Street',
        'zip' => '98004',
        'state' => 'WA'
    )
);

# Authorize transaction
$response = $gateway->authorize('100', $creaditCard, $options);
if ($response->success()) {
    echo 'Success Authorize';
} else {
    echo $response->message();
}

```
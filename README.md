GrimmlinkAlipayBundle
=================
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4889bbb4-274b-43a5-be42-be1462665222/mini.png)](https://insight.sensiolabs.com/projects/4889bbb4-274b-43a5-be42-be1462665222)

A Symfony Bundle to help you create & submit payment using Alipay's Cross-border Website Payment system (http://global.alipay.com/product/websitepayment.htm)

Requirements
------------

 * cURL
 * openssl

Installation
------------

Installation with composer :

```json
    ...
    "require": {
        ...
        "grimmlink/alipay-bundle": "dev-master",
        ...
    },
    ...
```

Add this bundle to your app/AppKernel.php :

``` php
public function registerBundles()
{
    return array(
        // ...
        new Grimmlink\AlipayBundle\GrimmlinkAlipayBundle(),
        // ...
    );
}
```

Configuration
-------------

Your personnal account informations must be set in your config.yml

```yml
# Grimmlink Alipay Bundle
grimmlink_alipay:
    config:
        https_verify_url:   https://mapi.alipay.net/gateway.do          # The API endpoint
        key:                'abcdefghijklmnopqrstuvwxyz123456'          # Your account key - alipay testing environment: 760bdzec6y9goq7ctyx96ezkz78287de
    parameters:
        partner:            '1234567890123456'                          # Your partner number - alipay testing environment: 2088101122136241
        currency:           EUR                                         # Cannot be CNY / RMB !!
        input_charset:      utf-8                                       # 
        service:            create_forex_trade                          # 
```

Routing
-------

```yml
# Grimmlink Alipay Bundle
grimmlink_alipay:
    resource: '@GrimmlinkAlipayBundle/Resources/config/routing.yml'
```

Usage
-----

In your Payment controller :

```php
...
    $apr = $this->get('grimmlink_alipay.request_handler');
    $apr->setParameters(array(
        'notify_url'        => $this->generateUrl('grimmlink_alipay_notify', array(), true),
        'return_url'        => $this->generateUrl('payment_alipay_return', array(), true), // make it your own
        'out_trade_no'      => $ref,  // make it your own
        'total_fee'         => '0.01', // make it your own
        'subject'           => 'Payment title', // make it your own
        'body'              => 'This is the payment description', // make it your own
    ));
    
    return $this->render('PaymentBundle:Alipay:pay.html.twig', array(
        'url'       => $apr->getUrl(),
        'form'      => $apr->getForm()->createView(),
    ));
...
```

Do not forget to create the controller action for your return URL, and do not include payment treatment inside, this is done in the Notification event listener.

Then, create your own Alipay Notification service listener, like this :

```yml
services:
    ...
    alipay.notification:
        class: Grimmlink\PaymentBundle\EventListener\AlipayListener
        tags:
            - { name: kernel.event_listener, event: alipay.notify_response, method: onAlipayNotificationResponse }
```

Create your Listener class and put your payment logic inside
The AlipayResponseEvent contains
- data : the Query parameters sent by Alipay
- response : "true" if  the notification has been validated
- signed : boolean - is the Alipay response's signature valid
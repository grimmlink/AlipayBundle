<?php

namespace Grimmlink\AlipayBundle\Tests\Alipay;

use Grimmlink\AlipayBundle\Alipay\Request;
use Grimmlink\AlipayBundle\Alipay\Core;

/**
 * Class RequestTest
 *
 * @package Grimmlink\AlipayBundle\Tests\Alipay
 *
 * @author Guillaume Fremont <grimmlink@gmail.com>
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    private $_alipay;

    protected function setUp()
    {
        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $this->_alipay = new Request($formFactory, array(
            'partner'           => '2088201564874474',
            'input_charset'    => 'UTF-8',
            
            'key'               => '1236464654987984654651321321',
            'sign_type'         => 'MD5',
            'transport'         => 'http',
            
            'service'           => 'create_direct_pay_by_user',
            'payment_type'      => 1,
            'seller_email'      => 'grimmlink@gmail.com',
            'anti_phishing_key' => '',
            'exter_invoke_ip'   => '',
        ));
        
        $this->_alipay->setParameter('notify_url',    'http://www.domain.com/notify');
        $this->_alipay->setParameter('return_url',    'http://www.domain.com/return');
        $this->_alipay->setParameter('out_trade_no',  '12345678901234567890123456789012');
        $this->_alipay->setParameter('total_fee',     '0.01');
        $this->_alipay->setParameter('subject',       'Test Payment');
        $this->_alipay->setParameter('body',          'Payment for testing the bundle');
    }

    protected function tearDown()
    {
        $this->_alipay = null;
    }

    public function testInitParameters()
    {
        $this->assertEquals(2088201564874474, $this->_alipay->getParameter('partner'));
        $this->assertEquals('UTF-8', $this->_alipay->getParameter('_input_charset'));
        
        $this->assertEquals('create_direct_pay_by_user', $this->_alipay->getParameter('service'));
        $this->assertEquals(1, $this->_alipay->getParameter('payment_type'));
        $this->assertEquals('grimmlink@gmail.com', $this->_alipay->getParameter('seller_email'));
        $this->assertEquals('', $this->_alipay->getParameter('anti_phishing_key'));
        $this->assertEquals('', $this->_alipay->getParameter('exter_invoke_ip'));
        
        $this->assertEquals('http://www.domain.com/notify', $this->_alipay->getParameter('notify_url'));
        $this->assertEquals('http://www.domain.com/return', $this->_alipay->getParameter('return_url'));
        $this->assertEquals('12345678901234567890123456789012', $this->_alipay->getParameter('out_trade_no'));
        $this->assertEquals('0.01', $this->_alipay->getParameter('total_fee'));
        $this->assertEquals('Test Payment', $this->_alipay->getParameter('subject'));
        $this->assertEquals('Payment for testing the bundle', $this->_alipay->getParameter('body'));
    }

    public function testSetParameter()
    {
        $this->_alipay->setParameter('_input_charset', 'GBK');
        $this->assertEquals($this->_alipay->getParameter('_input_charset'), 'GBK');

        $this->assertNull($this->_alipay->getParameter('show_url'));
    }

    public function testSetParameters()
    {
        $this->_alipay->setParameters(array(
            '_input_charset' => 'UTF-8',
        ));

        $this->assertEquals($this->_alipay->getParameter('_input_charset'), 'UTF-8');
    }

    public function testGetParameters()
    {
        $this->assertTrue(null === $this->_alipay->getParameter('show_url'));

        $parameters = $this->_alipay->getParameters();

        $this->assertTrue(isset($parameters['_input_charset']));
    }

    public function testBuildSign()
    {
        $params = $this->_alipay->buildParameters($this->_alipay->getParameters());
        
        $parameters = $this->_alipay->getParameters();
        unset($parameters['sign']);
        unset($parameters['sign_type']);
        unset($parameters['key']);
        unset($parameters['transport']);
        unset($parameters['anti_phishing_key']);
        unset($parameters['exter_invoke_ip']);
        ksort($parameters);
        reset($parameters);
        
        $qs = Core::toQueryString($parameters);
        $sign = md5($qs . '1236464654987984654651321321');
        
        $this->assertTrue($sign === $params['sign']);
    }
}

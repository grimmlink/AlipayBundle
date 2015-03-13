<?php

namespace Grimmlink\AlipayBundle\Alipay

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;

use Grimmlink\AlipayBundle\Event\AlipayEvents;
use Grimmlink\AlipayBundle\Event\AlipayResponseEvent;
use Grimmlink\AlipayBundle\Alipay\Core;

/**
 * Class Response
 *
 * @package Grimmlink\AlipayBundle\Alipay
 *
 * @author Guillaume Fremont <grimmlink@gmail.com>
 */
class Response
{
    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $signature;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var array
     */
    private $config;
    
    /**
     * Contructor.
     *
     * @param RequestStack             $request_stack
     * @param EventDispatcherInterface $dispatcher
     * @param array                    $parameters
     * @param array                    $config
     */
    public function __construct(RequestStack $request_stack, EventDispatcherInterface $dispatcher, array $parameters, array $config)
    {
        $this->request = $request_stack->getCurrentRequest();
        $this->dispatcher = $dispatcher;
        $this->parameters = $parameters;
        $this->config = $config;
    }
    
    /**
     * Verifies the validity of the signature.
     * Possible Trade Status:
     *  WAIT_BUYER_PAY - wait for buyer to pay
     *  TRADE_CLOSED - transaction timed out
     *  TRADE_SUCCESS - payment was successful, refunds allowed
     *  TRADE_PENDING - waiting for buyer to pay
     *  TRADE_FINISHED - payment was successful, no refunds allowed
     *
     * @return bool
     */
    public function verify()
    {
        $query_parameters = $this->request->request->all();
        $isSigned = $this->verifySign($query_parameters);
        
        $response = 'true';
        if ($this->request->request->has('notify_id')) {
            $response = $this->getNotifyResponse($this->request->request->get('notify_id'));
        }
        
        $event = new AlipayResponseEvent($query_parameters, $response, $isSigned);
        $this->dispatcher->dispatch(AlipayEvents::ALIPAY_NOTIFY_RESPONSE, $event);
        
        if (preg_match("/true$/i", $response) && $isSigned) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Verifies the validity of the signature.
     *
     * @param array $parameters
     *
     * @return bool
     */
    private function verifySign($query_parameters)
    {
        $sorted_params = Core::sortParameters($query_parameters);
        
        $sign = $this->buildSign($sorted_params, $this->config['key']);
        $isSigned = ($sign == $query_parameters['sign']);
        
        return $isSigned;
    }
    
    /**
     * Build sign string
     *
     * @param array  $parameters
     * @param string $key
     *
     * @return string
     */
    private function buildSign($parameters, $key)
    {
        $query_string = self::toQueryString($parameters);
        $sign = md5($query_string . $key);
        
        return $sign;
    }

    private function getNotifyResponse($notify_id) {
        $notify_parameters = array(
            'partner'       => $this->parameters['partner'],
            'notify_id'    => $notify_id
        );
        
        if ($this->config['transport'] == 'https') {
            $verify_url = $this->config['https_verify_url'];
            $notify_parameters['service'] = 'notify_verify';
        } else {
            $verify_url = $this->config['http_verify_url'];
        }
        
        Core::request($verify_url, $notify_parameters);
    }
}

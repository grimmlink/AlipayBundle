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
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $signature;

    /**
     * @var array
     */
    private $parameters;
    
    /**
     * @var string
     */
    private $https_verify_url = 'https://mapi.alipay.com/gateway.do';
    
    /**
     * @var string
     */
    private $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do';
    
    /**
     * Contructor.
     *
     * @param RequestStack             $request_stack
     * @param EventDispatcherInterface $dispatcher
     * @param array                    $parameters
     */
    public function __construct(RequestStack $request_stack, EventDispatcherInterface $dispatcher, array $parameters)
    {
        $this->request    = $request_stack->getCurrentRequest();
        $this->dispatcher = $dispatcher;
        $this->parameters = $parameters;
    }
    
    /**
     * Verifies the validity of the signature.
     *
     * @return bool
     */
    public function verify()
    {
        $isSigned = $this->verifySign($this->request->request->all());
        
        $response = 'true';
        if ($this->request->request->has('notify_id')) {
            $response = $this->getNotifyResponse($this->request->request->get('notify_id'));
        }
        
        $event = new AlipayResponseEvent($this->data, $isSigned);
        $this->dispatcher->dispatch(AlipayEvents::ALIPAY_NOTIFY_RESPONSE, $event);
        
        if ($response === 'true' && $isSigned) {
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
        $filtered_parameters = Core::filterParameters($query_parameters);
        $sorted_params = Core::sortParameters($filtered_parameters);
        
        $sign = $this->buildSign($sorted_params, $query_parameters['sign_type'], $this->parameters['key']);
        
        $isSigned = false;
        switch ($this->parameters['sign_type']) {
            case "MD5" :
                $isSigned = ($sign == $query_parameters['sign']);
                break;
            default :
                break;
        }
        
        return $isSigned;
    }
    
    /**
     * Build sign string
     *
     * @param array  $parameters
     * @param string $sign_type
     * @param string $key
     *
     * @return string
     */
    private function buildSign($parameters, $sign_type, $key)
    {
        $query_string = self::toQueryString($parameters);
        
        $sign = "";
        switch ($sign_type) {
            case "MD5" :
                $sign = md5($query_string . $key);
                break;
            default :
                $sign = "";
        }
        
        return $sign;
    }

    private function getNotifyResponse($notify_id) {
        $notify_parameters = array(
            'partner'       => $this->parameters['partner'],
            'notify_id'    => $notify_id
        );
        
        if ($this->parameters['transport'] == 'https') {
            $verify_url = $this->https_verify_url;
            $notify_parameters['service'] = 'notify_verify';
        } else {
            $verify_url = $this->http_verify_url;
        }
        
        Core::request($verify_url, $notify_parameters);
    }
}

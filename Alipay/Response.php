<?php

namespace Grimmlink\AlipayBundle\Alipay;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Grimmlink\AlipayBundle\Event\AlipayEvents;
use Grimmlink\AlipayBundle\Event\AlipayResponseEvent;

/**
 * Class Response.
 *
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
     * @var FileLocator
     */
    private $file_locator;

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
    public function __construct(RequestStack $request_stack, EventDispatcherInterface $dispatcher, FileLocator $file_locator, array $parameters, array $config)
    {
        $this->request = $request_stack->getCurrentRequest();
        $this->dispatcher = $dispatcher;
        $this->file_locator = $file_locator;
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
     *  TRADE_FINISHED - payment was successful, no refunds allowed.
     *
     * @return bool
     */
    public function verify()
    {
        $query_parameters = $this->request->request->all();
        $isVerified = $this->verifySign($query_parameters);

        $response = 'true';
        if ($this->request->request->has('notify_id')) {
            $response = $this->getNotifyResponse($this->request->request->get('notify_id'));
        }

        $event = new AlipayResponseEvent($query_parameters, $response, $isVerified);
        $this->dispatcher->dispatch(AlipayEvents::ALIPAY_NOTIFY_RESPONSE, $event);

        if (preg_match("/true$/i", $response) && $isVerified) {
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
        $query_string = Core::toQueryString($sorted_params);

		switch (strtoupper(trim($this->parameters['sign_type']))) {
			case "RSA":
				$verified = Core::rsaVerify($query_string, $this->config['alipay_public_key'], $query_parameters['sign']);
				break;
			default:
				$verified = md5($query_string.$this->config['key']) == $parameters['sign'];
		}

        return $verified;
    }

    private function getNotifyResponse($notify_id)
    {
        $notify_parameters = array(
            'service'       => 'notify_verify',
            'partner'       => $this->parameters['partner'],
            'notify_id'     => $notify_id,
        );

        $cacert = $this->file_locator->locate('@GrimmlinkAlipayBundle/Resources/alipay_cacert.pem');

        return Core::request($this->config['https_verify_url'], $notify_parameters, $cacert);
    }
}

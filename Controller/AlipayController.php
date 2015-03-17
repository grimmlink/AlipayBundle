<?php

namespace Grimmlink\AlipayBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AlipayController
 *
 * @package Grimmlink\AlipayBundle\Controller
 *
 * @author Guillaume Fremont <grimmlink@gmail.com>
 */
class AlipayController extends Controller
{
    /**
     * Receive the notification from Alipay
     *
     * @return Response
     */
    public function notifyAction()
    {
        $response = $this->container->get('grimmlink_alipay.response_handler');
        $verification = $response->verify();

        return new Response($verification ? 'success' : 'fail');
    }
}

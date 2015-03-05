<?php

namespace Grimmlink\AlipayBundle\Event;

/**
 * AlipayEvents class.
 *
 * @author Guillaume Fremont <grimmlink@gmail.com>
 */
class AlipayEvents
{
    /**
     * Trigered for each alipay response
     *
     * @var string
     */
    const ALIPAY_NOTIFY_RESPONSE = 'alipay.notify_response';
}

<?php

namespace Grimmlink\AlipayBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * AlipayResponseEvent class.
 *
 * @author Guillaume Fremont <grimmlink@gmail.com>
 */
class AlipayResponseEvent extends Event
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var boolean
     */
    private $signed;

    /**
     * Constructor.
     *
     * @param array   $data
     * @param boolean $signed
     */
    public function __construct(array $data, $signed = false)
    {
        $this->data = $data;
        $this->signed = (bool) $signed;
    }

    /**
     * Returns all parameters sent on IPN.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns true if signature verification was successful.
     *
     * @return boolean
     */
    public function isSigned()
    {
        return $this->signed;
    }
}

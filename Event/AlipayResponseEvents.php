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
     * @var mixed
     */
    private $response;

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
    public function __construct(array $data, $response, $signed = false)
    {
        $this->data = $data;
        $this->response = $response;
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
     * Returns the response.
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
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

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
    private $verified;

    /**
     * Constructor.
     *
     * @param array   $data
     * @param boolean $verified
     */
    public function __construct(array $data, $response, $verified = false)
    {
        $this->data = $data;
        $this->response = $response;
        $this->verified = (bool) $verified;
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
    public function isVerified()
    {
        return $this->verified;
    }
}

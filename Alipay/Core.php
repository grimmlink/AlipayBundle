<?php

namespace Grimmlink\AlipayBundle\Alipay;

use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Message\Response;

/**
 * Class Core
 *
 * @package Grimmlink\AlipayBundle\Alipay
 *
 * @author Guillaume Fremont <grimmlink@gmail.com>
 */
class Core
{
    /**
     * Returns filtered parameters
     *
     * @param array $parameters
     *
     * @return array
     */
    static public function filterParameters($parameters)
    {
        $filtered_parameters = array();
        foreach ($parameters as $key => $val) {
            if (in_array($key, array('sign', 'sign_type')) || $val == "") {
                continue;
            } else {
                $filtered_parameters[$key] = $val;
            }
        }
        
        return $filtered_parameters;
    }
    
    /**
     * Returns sorted parameters
     *
     * @param array $parameters
     *
     * @return array
     */
    static public function sortParameters($parameters)
    {
        $parameters = self::filterParameters($parameters);
        ksort($parameters);
        reset($parameters);
        
        return $parameters;
    }
    
    /**
     * Transform an array into a querystring
     *
     * @param array $parameters
     *
     * @return string
     */
    static public function toQueryString(array $parameters)
    {
        $query_array = array();
        
        foreach ($parameters as $key => $val) {
            $query_array[] = sprintf('%s=%s', $key, $val);
        }
        
        $query_string = implode('&', $query_array);
        
        return $query_string;
    }
    
    /**
     * Transform an array into an encoded querystring
     *
     * @param array $parameters
     *
     * @return string
     */
    static public function toEncodedQueryString(array $parameters)
    {
        $query_array = array();
        
        foreach ($parameters as $key => $val) {
            $query_array[] = sprintf('%s=%s', $key, urlencode($val));
        }
        
        $query_string = implode('&', $query_array);
        
        return $query_string;
    }
    
    /**
     * Transform an array into an encoded querystring
     *
     * @param array $parameters
     *
     * @return string
     */
    static public function request($endpoint, $parameters)
    {
        $browser = new Browser(new Curl());
        $response = $browser->submit($endpoint, $parameters);
        
        if ($response->isSuccessful()) {
            return $response->getContent();
        } else {
            return false;
        }
    }
}

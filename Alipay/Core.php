<?php

namespace Grimmlink\AlipayBundle\Alipay;

/**
 * Class Core.
 *
 *
 * @author Guillaume Fremont <grimmlink@gmail.com>
 */
class Core
{
    /**
     * Returns filtered parameters.
     *
     * @param array $parameters
     *
     * @return array
     */
    public static function filterParameters($parameters)
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
     * Returns sorted parameters.
     *
     * @param array $parameters
     *
     * @return array
     */
    public static function sortParameters($parameters)
    {
        $parameters = self::filterParameters($parameters);
        ksort($parameters);
        reset($parameters);

        return $parameters;
    }

    /**
     * Transform an array into a querystring.
     *
     * @param array $parameters
     *
     * @return string
     */
    public static function toQueryString(array $parameters)
    {
        $query_array = array();

        foreach ($parameters as $key => $val) {
            $query_array[] = sprintf('%s=%s', $key, $val);
        }

        $query_string = implode('&', $query_array);

        return $query_string;
    }

    /**
     * Transform an array into an encoded querystring.
     *
     * @param array $parameters
     *
     * @return string
     */
    public static function toEncodedQueryString(array $parameters)
    {
        $query_array = array();

        foreach ($parameters as $key => $val) {
            $query_array[] = sprintf('%s=%s', $key, urlencode($val));
        }

        $query_string = implode('&', $query_array);

        return $query_string;
    }

    /**
     * Transform an array into an encoded querystring.
     *
     * @param array $parameters
     *
     * @return string
     */
    public static function request($endpoint, $parameters, $cacert)
    {
        $url = $endpoint.'?'.self::toQueryString($parameters);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST,            false);
        curl_setopt($curl, CURLOPT_HEADER,          0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,  1);
        curl_setopt($curl, CURLOPT_SSLVERSION,      3);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,  true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,  2);
        curl_setopt($curl, CURLOPT_CAINFO,          $cacert);
        $response = curl_exec($curl);

        $return = curl_error($curl) ? false : $response;
        curl_close($curl);

        return $return;
    }
}

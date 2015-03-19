<?php

namespace Grimmlink\AlipayBundle\Alipay;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class Request.
 *
 *
 * @author Guillaume Fremont <grimmlink@gmail.com>
 */
class Request
{
    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $config;

    /**
     * Construct method.
     *
     * @param FormFactoryInterface $factory
     * @param array                $parameters
     * @param array                $config
     */
    public function __construct(FormFactoryInterface $factory, array $parameters, array $config)
    {
        $this->factory = $factory;
        $this->config = $config;

        $this->init($parameters);
    }

    /**
     * Sets the config parameters.
     *
     * @param array $parameters
     */
    protected function init(array $parameters)
    {
        $this->parameters = array(
            '_input_charset'    => $parameters['input_charset'],
            'service'           => $parameters['service'],
            'partner'           => $parameters['partner'],
            'currency'          => $parameters['currency'],
            'sign_type'         => 'MD5',

            // 'notify_url'        => $notify_url,
            // 'return_url'        => $return_url,
            // 'subject'           => $subject,
            // 'body'              => $body,
            // 'out_trade_no'      => $out_trade_no,
            // 'total_fee'         => $total_fee,
        );
    }

    /**
     * Gets a parameter.
     *
     * @param string $name
     *
     * @return string
     */
    public function getParameter($name)
    {
        return (isset($this->parameters[$name])) ? $this->parameters[$name] : null;
    }

    /**
     * Sets a parameter.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return Request
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * Returns all parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Sets multiple parameters.
     *
     * @param array $parameters
     *
     * @return Request
     */
    public function setParameters($parameters)
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value);
        }

        return $this;
    }

    /**
     * Returns a form with defined parameters.
     *
     * @param array $options
     *
     * @return Form
     */
    public function getForm($options = array())
    {
        $options['csrf_protection'] = false;

        $parameters = $this->buildParameters($this->parameters);
        $builder = $this->factory->createNamedBuilder('', 'form', $parameters, $options);

        foreach ($parameters as $key => $value) {
            $builder->add($key, 'hidden');
        }

        return $builder->getForm();
    }

    /**
     * Returns all parameters set for a payment.
     *
     * @param array $parameters
     *
     * @return array
     */
    public function buildParameters($parameters)
    {
        $sorted_params = Core::sortParameters($parameters);

        $sorted_params['sign'] = $this->buildSign($sorted_params, $this->config['key']);
        $sorted_params['sign_type'] = $this->parameters['sign_type'];

        return $sorted_params;
    }

    /**
     * Build sign string.
     *
     * @param array  $parameters
     * @param string $key
     *
     * @return string
     */
    public function buildSign($parameters, $key)
    {
        $query_string = Core::toQueryString($parameters);
        $sign = md5($query_string.$key);

        return $sign;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return $this->config['https_verify_url'];
    }
}

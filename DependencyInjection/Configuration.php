<?php

namespace Grimmlink\AlipayBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('grimmlink_alipay');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('parameters')
                    ->isRequired()
                    ->children()
                        ->scalarNode('partner')->isRequired()->end()
                        ->scalarNode('key')->isRequired()->end()
                        ->scalarNode('sign_type')->defaultValue('MD5')->end()
                        ->scalarNode('input_charset')->defaultValue('UTF-8')->end()
                        ->scalarNode('transport')->defaultValue('http')->end()
                        
                        ->scalarNode('service')->defaultValue('create_direct_pay_by_user')->end()
                        ->scalarNode('payment_type')->defaultValue(1)->end()
                        ->scalarNode('seller_email')->isRequired()->end()
                        ->scalarNode('anti_phishing_key')->defaultValue('')->end()
                        ->scalarNode('exter_invoke_ip')->defaultValue('')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

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
                ->arrayNode('config')
                    ->isRequired()
                    ->children()
                        ->scalarNode('https_verify_url')->defaultValue('https://mapi.alipay.com/gateway.do')->end()
                        ->scalarNode('key')->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('parameters')
                    ->isRequired()
                    ->children()
                        ->scalarNode('partner')->isRequired()->end()
                        ->scalarNode('currency')->defaultValue('EUR')->end()
                        ->scalarNode('input_charset')->defaultValue('UTF-8')->end()
                        ->scalarNode('service')->defaultValue('create_direct_pay_by_user')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

<?php

namespace BSP\AdyenBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bsp_adyen', 'array');

		$rootNode
			->children()
  	          	->scalarNode('platform')
					->validate()
						->ifNotInArray(array('live', 'test'))
						->thenInvalid('The %s platform is not supported')
					->end()
				->end()
				->scalarNode('merchant_account')->isRequired()->end()
				->scalarNode('skin')->isRequired()->end()
				->scalarNode('shared_secret')->isRequired()->end()
				->scalarNode('currency')->defaultValue('EUR')->end()
				->arrayNode('payment_methods')
					->prototype('scalar')->end()
				->end()
				->scalarNode('webservice_username')->isRequired()->end()
				->scalarNode('webservice_password')->isRequired()->end()
			->end();
        
        return $treeBuilder;
    }
}

<?php

namespace BSP\AdyenBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BSPAdyenExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $container->setParameter('adyen.platform', $config['platform']);
        $container->setParameter('adyen.skin', $config['skin']);
        $container->setParameter('adyen.merchant_account', $config['merchant_account']);
        $container->setParameter('adyen.shared_secret', $config['shared_secret']);
        $container->setParameter('adyen.currency', $config['currency']);
        $container->setParameter('adyen.payment_methods', $config['payment_methods']);
        $container->setParameter('adyen.webservice_username', $config['webservice_username']);
        $container->setParameter('adyen.webservice_password', $config['webservice_password']);
    }
    
    public function getXsdValidationBasePath()
    {
    	return __DIR__.'/../Resources/config/';
    }
}

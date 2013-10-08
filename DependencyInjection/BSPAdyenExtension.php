<?php

namespace BSP\AdyenBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

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

        $container->setParameter('bsp.adyen.platform', $config['platform']);
        $container->setParameter('bsp.adyen.skin', $config['skin']);
        $container->setParameter('bsp.adyen.merchant_account', $config['merchant_account']);
        $container->setParameter('bsp.adyen.shared_secret', $config['shared_secret']);
        $container->setParameter('bsp.adyen.currency', $config['currency']);
        $container->setParameter('bsp.adyen.payment_methods', $config['payment_methods']);
        $container->setParameter('bsp.adyen.webservice_username', $config['webservice_username']);
        $container->setParameter('bsp.adyen.webservice_password', $config['webservice_password']);
    }

    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/';
    }
}

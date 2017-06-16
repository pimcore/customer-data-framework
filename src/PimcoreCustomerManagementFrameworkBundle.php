<?php

namespace CustomerManagementFrameworkBundle;


use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class PimcoreCustomerManagementFrameworkBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/bundles/customermanagementframework/js/startup.js',
            '/bundles/customermanagementframework/js/ActivityView.js',
            '/bundles/customermanagementframework/js/CustomerView.js',
            '/bundles/customermanagementframework/js/CustomerView.js',
            '/bundles/customermanagementframework/js/config/panel.js',
            '/bundles/customermanagementframework/js/config/rule.js',
            '/bundles/customermanagementframework/js/config/trigger.js',
            '/bundles/customermanagementframework/js/config/conditions.js',
            '/bundles/customermanagementframework/js/config/actions.js',
            '/bundles/customermanagementframework/js/config/actions.js',
            '/bundles/customermanagementframework/js/pimcore/report/custom/definitions/termSegmentBuilder.js',
        ];
    }

    public function getCssPaths()
    {
        return [
            '/bundles/customermanagementframework/css/pimcore.css',
        ];
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__ . '../../Resources/config')
        );

        $loader->load('services.yml');
    }
}

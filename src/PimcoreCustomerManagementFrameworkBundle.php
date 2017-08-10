<?php

namespace CustomerManagementFrameworkBundle;

use CustomerManagementFrameworkBundle\DependencyInjection\Compiler\OAuthUtilsPass;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PimcoreCustomerManagementFrameworkBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/bundles/pimcorecustomermanagementframework/js/startup.js',
            '/bundles/pimcorecustomermanagementframework/js/ActivityView.js',
            '/bundles/pimcorecustomermanagementframework/js/CustomerView.js',
            '/bundles/pimcorecustomermanagementframework/js/CustomerView.js',
            '/bundles/pimcorecustomermanagementframework/js/config/panel.js',
            '/bundles/pimcorecustomermanagementframework/js/config/rule.js',
            '/bundles/pimcorecustomermanagementframework/js/config/trigger.js',
            '/bundles/pimcorecustomermanagementframework/js/config/conditions.js',
            '/bundles/pimcorecustomermanagementframework/js/config/actions.js',
            '/bundles/pimcorecustomermanagementframework/js/config/actions.js',
            '/bundles/pimcorecustomermanagementframework/js/pimcore/report/custom/definitions/termSegmentBuilder.js',
        ];
    }

    public function getCssPaths()
    {
        return [
            '/bundles/pimcorecustomermanagementframework/css/pimcore.css',
        ];
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OAuthUtilsPass());
    }

    /**
     * @return Installer
     */
    public function getInstaller()
    {
        return new Installer();
    }
}

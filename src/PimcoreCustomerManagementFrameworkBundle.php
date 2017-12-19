<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle;

use CustomerManagementFrameworkBundle\DependencyInjection\Compiler\CustomerSaveManagerPass;
use CustomerManagementFrameworkBundle\DependencyInjection\Compiler\NewsletterManagerPass;
use CustomerManagementFrameworkBundle\DependencyInjection\Compiler\OAuthUtilsPass;
use CustomerManagementFrameworkBundle\DependencyInjection\Compiler\SegmentManagerPass;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PimcoreCustomerManagementFrameworkBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/admin/customermanagementframework/helper/settings-json',
            '/bundles/pimcorecustomermanagementframework/js/startup.js',
            '/bundles/pimcorecustomermanagementframework/js/ActivityView.js',
            '/bundles/pimcorecustomermanagementframework/js/CustomerView.js',
            '/bundles/pimcorecustomermanagementframework/js/SegmentAssignmentView.js',
            '/bundles/pimcorecustomermanagementframework/js/config/panel.js',
            '/bundles/pimcorecustomermanagementframework/js/config/rule.js',
            '/bundles/pimcorecustomermanagementframework/js/config/trigger.js',
            '/bundles/pimcorecustomermanagementframework/js/config/conditions.js',
            '/bundles/pimcorecustomermanagementframework/js/config/actions.js',
            '/bundles/pimcorecustomermanagementframework/js/config/actions.js',
            '/bundles/pimcorecustomermanagementframework/js/gdprDataProvider/customers.js',
            '/bundles/pimcorecustomermanagementframework/js/pimcore/report/custom/definitions/termSegmentBuilder.js'
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
        $container->addCompilerPass(new CustomerSaveManagerPass());
        $container->addCompilerPass(new SegmentManagerPass());
        $container->addCompilerPass(new NewsletterManagerPass());
    }


    public function getInstaller()
    {
        return $this->container->get(Installer::class);
    }
}

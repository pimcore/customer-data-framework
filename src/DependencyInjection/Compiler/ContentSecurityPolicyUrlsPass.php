<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under following license:
 * - Pimcore Commercial License (PCL)
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     PCL
 */

namespace CustomerManagementFrameworkBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContentSecurityPolicyUrlsPass implements CompilerPassInterface
{
    /**
     * Registers each service with tag translations-provider-interface-bundle.data-changed-handler as data changed handler.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('Pimcore\\Bundle\\AdminBundle\\Security\\ContentSecurityPolicyHandler')) {
            return;
        }

        $definition = $container->getDefinition('Pimcore\\Bundle\\AdminBundle\\Security\\ContentSecurityPolicyHandler');

        $definition->addMethodCall('addAllowedUrls', ['script-src', [
            'https://maxcdn.bootstrapcdn.com/bootstrap/'
        ]]);
    }
}

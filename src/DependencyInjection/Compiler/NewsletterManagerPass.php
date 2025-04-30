<?php

declare(strict_types=1);

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\DependencyInjection\Compiler;

use CustomerManagementFrameworkBundle\Newsletter\Manager\NewsletterManagerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class NewsletterManagerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds('cmf.newsletter_provider_handler');

        if (sizeof($taggedServices)) {
            if (!$container->hasDefinition(NewsletterManagerInterface::class)) {
                throw new \Exception('CMF newsletter services are not enabled in the config file but a newsletter provider handler is registered as service.');
            }

            $definition = $container->getDefinition(NewsletterManagerInterface::class);

            foreach ($taggedServices as $id => $tags) {
                $definition->addMethodCall('addNewsletterProviderHandler', [new Reference($id)]);
            }
        }
    }
}

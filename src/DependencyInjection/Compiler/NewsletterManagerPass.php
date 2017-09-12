<?php

declare(strict_types=1);

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\DependencyInjection\Compiler;

use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFrameworkBundle\Newsletter\Manager\NewsletterManagerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class NewsletterManagerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {

        $taggedServices = $container->findTaggedServiceIds('cmf.newsletter_provider_handler');

        if(sizeof($taggedServices)) {
            if (!$container->hasDefinition(NewsletterManagerInterface::class)) {
                throw new \Exception('CMF newsletter services are not enabled in the config file but a newsletter provider handler is registered as service.');
            }

            $definition = $container->getDefinition( NewsletterManagerInterface::class);


            foreach ($taggedServices as $id => $tags) {

                $definition->addMethodCall('addNewsletterProviderHandler', [new Reference($id)]);
            }
        }
    }
}

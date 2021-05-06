<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\DependencyInjection\Compiler;

use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SegmentManagerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(SegmentManagerInterface::class);

        $taggedServices = $container->findTaggedServiceIds('cmf.segment_builder');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addSegmentBuilder', [new Reference($id)]);
        }
    }
}

<?php

declare(strict_types=1);

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

namespace CustomerManagementFrameworkBundle\DependencyInjection\Compiler;

use CustomerManagementFrameworkBundle\Security\OAuth\OAuthUtils;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OAuthUtilsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // update definition to use own OAuthUtils class
        if ($container->hasDefinition('hwi_oauth.security.oauth_utils')) {
            $definition = $container->getDefinition('hwi_oauth.security.oauth_utils');
            $definition->setClass(OAuthUtils::class);
        }
    }
}

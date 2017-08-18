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

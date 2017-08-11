<?php

declare(strict_types=1);

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

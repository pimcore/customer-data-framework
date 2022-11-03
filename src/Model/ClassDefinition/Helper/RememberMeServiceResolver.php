<?php

namespace CustomerManagementFrameworkBundle\Model\ClassDefinition\Helper;

use Pimcore\Model\DataObject\ClassDefinition\Helper\ClassResolver;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;

class RememberMeServiceResolver extends ClassResolver
{

    public static function resolveRememberMeService(array $definitions): ?RememberMeServicesInterface
    {
        foreach ($definitions as $definition) {
            try {
                return self::resolveServiceFromDefinition($definition);
            } catch (\Throwable) {}
        }

        return null;
    }

    protected static function resolveServiceFromDefinition(string $definition): ?RememberMeServicesInterface
    {
        /** @var RememberMeServicesInterface $object */
        $object = self::resolve('@' . $definition, static fn(RememberMeServicesInterface $rememberMeServices) => true );
        return $object;
    }

}

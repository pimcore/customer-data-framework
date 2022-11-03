<?php

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
            } catch (\Throwable) {
            }
        }

        return null;
    }

    protected static function resolveServiceFromDefinition(string $definition): ?RememberMeServicesInterface
    {
        /** @var RememberMeServicesInterface $object */
        $object = self::resolve('@' . $definition, static fn (RememberMeServicesInterface $rememberMeServices) => true);

        return $object;
    }
}

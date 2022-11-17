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

namespace CustomerManagementFrameworkBundle\Model\ClassDefinition\Helper;

use Pimcore;
use Pimcore\Model\DataObject\ClassDefinition\Helper\ClassResolver;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class UserCheckerClassResolver extends ClassResolver
{
    public static function resolveUserChecker(string $serviceName): ?UserCheckerInterface
    {
        $container = Pimcore::getKernel()->getContainer();

        $userChecker = $container->get($serviceName);

        if (!$userChecker instanceof UserCheckerInterface) {
            return null;
        }

        return $userChecker;
    }
}

<?php

declare(strict_types=1);

namespace CustomerManagementFrameworkBundle\Model\ClassDefinition\Helper;

use Pimcore\Model\DataObject\ClassDefinition\Helper\ClassResolver;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class UserCheckerClassResolver extends ClassResolver
{
    public static function resolveUserChecker(string $serviceName): UserCheckerInterface
    {
        /** @var UserCheckerInterface $userChecker */
        $userChecker = self::resolve('@' . $serviceName, static function ($serviceObject)  {
            return $serviceObject instanceof UserCheckerInterface;
        });
        return $userChecker;
    }

}

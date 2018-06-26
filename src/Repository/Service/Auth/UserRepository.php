<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace CustomerManagementFrameworkBundle\Repository\Service\Auth;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use OAuth2ServerExamples\Entities\UserEntity;

class UserRepository implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {

        $customerProvider = \Pimcore::getContainer()->get(CustomerProviderInterface::class);
        $currentUser = $customerProvider->getActiveCustomerByEmail($username);

        /**
         * @var DataObject\ClassDefinition\Data\Password $field
         */
        $passwordField = $currentUser->getClass()->getFieldDefinition('password');

        if(!$passwordField->verifyPassword($password, $currentUser)){
            return;
            //throw new HttpException(401, "AUTHORIZATION FAILED");
        }

        return $currentUser;
    }
}

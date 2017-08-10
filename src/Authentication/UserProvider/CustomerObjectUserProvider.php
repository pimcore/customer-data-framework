<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 11/07/2017
 * Time: 15:28
 */

namespace CustomerManagementFrameworkBundle\Authentication\UserProvider;

use CustomerManagementFrameworkBundle\Authentication\SsoIdentity\SsoIdentityServiceInterface;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Security\OAuth\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Pimcore\Model\Object\AbstractObject;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomerObjectUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    /**
     * @var CustomerProviderInterface
     */
    private $customerProvider;

    /**
     * @var SsoIdentityServiceInterface
     */
    private $ssoIdentityService;

    /**
     * @var string
     */
    protected $usernameField = 'email';

    public function __construct(
        CustomerProviderInterface $customerProvider,
        SsoIdentityServiceInterface $ssoIdentityService,
        string $usernameField = 'email'
    )
    {
        $this->customerProvider = $customerProvider;
        $this->ssoIdentityService = $ssoIdentityService;
        $this->usernameField = $usernameField;
    }

    /**
     * @inheritdoc
     */
    public function loadUserByUsername($username)
    {
        $list = $this->customerProvider->getList();
        $list->setCondition(sprintf('active = 1 and %s = ?', $this->usernameField), $username);

        if (!$customer = $list->current()) {
            throw new UsernameNotFoundException(sprintf('Customer "%s" was not found', $username));
        }

        return $customer;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $provider = $response->getResourceOwner()->getName();
        $username = $response->getUsername();

        $user = $this->ssoIdentityService->getCustomerBySsoIdentity(
            $provider,
            $username
        );

        if (null === $user || null === $username) {
            // the AccountNotLinkedException will allow the frontend to proceed to registration
            // and to fetch user data from the OAuth account
            $exception = new AccountNotLinkedException(sprintf(
                'No customer was found for user "%s" on provider "%s"',
                $username,
                $provider
            ));

            $exception->setUsername($username);

            throw $exception;
        }

        return $user;
    }

    /**
     * @inheritdoc
     */
    public function refreshUser(UserInterface $user)
    {
        $class = $this->customerProvider->getCustomerClassName();
        if (!$user instanceof $class || !$user instanceof AbstractObject) {
            throw new UnsupportedUserException();
        }

        return $this->customerProvider->getById($user->getId(), true);
    }

    /**
     * @inheritdoc
     */
    public function supportsClass($class)
    {
        if ($class === $this->customerProvider->getCustomerClassName()) {
            return true;
        }

        return false;
    }
}

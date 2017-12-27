<?php

namespace CustomerManagementFrameworkBundle\Security\OAuth\Exception;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AccountNotActiveException extends AuthenticationException
{
    /**
     * @var CustomerInterface $customer
     */
    protected $customer;

    /**
     * @return CustomerInterface
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param CustomerInterface $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    public function serialize()
    {
        return serialize([
            $this->customer,
            parent::serialize()
        ]);
    }

    public function unserialize($str)
    {
        list(
            $this->customer,
            $parentData
        ) = unserialize($str);

        parent::unserialize($parentData);
    }


}

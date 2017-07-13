<?php
/**
 * Created by PhpStorm.
 * User: jraab
 * Date: 01.09.2016
 * Time: 16:28
 */

namespace AppBundle\Model;



use CustomerManagementFrameworkBundle\Model\AbstractCustomer\DefaultAbstractUserawareCustomer;
use CustomerManagementFrameworkBundle\Model\SsoAwareCustomerInterface;

abstract class Customer extends DefaultAbstractUserawareCustomer implements SsoAwareCustomerInterface
{


}

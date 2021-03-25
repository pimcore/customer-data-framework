<?php

namespace CustomerManagementFrameworkBundle\Tests\Model\Customer;


use Pimcore\Model\DataObject\Customer;
use Pimcore\Tests\Test\ModelTestCase;
use Pimcore\Tests\Util\Autoloader;
use Pimcore\Tests\Util\TestHelper;

class CustomerTest extends ModelTestCase
{

    public function setUp()
    {
        parent::setUp();
        TestHelper::cleanUp();
    }

    public function tearDown()
    {
        TestHelper::cleanUp();
        parent::tearDown();
    }

    public function testCreateCustomer() {

        $customer = new Customer();
        $customer->setKey('foo');
        $customer->setPublished(true);
        $customer->setActive(true);
        $customer->setParentId(0);
        $customer->setFirstname('Peter');
        $customer->setLastname('Hugo');
        $customer->save();

    }

}

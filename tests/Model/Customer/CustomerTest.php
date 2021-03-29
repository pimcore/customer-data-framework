<?php

namespace CustomerManagementFrameworkBundle\Tests\Model\Customer;


use CustomerManagementFrameworkBundle\Listing\FilterHandler;
use Pimcore\Model\DataObject\Customer;
use Pimcore\Tests\Test\ModelTestCase;
use Pimcore\Tests\Util\Autoloader;
use Pimcore\Tests\Util\TestHelper;

class CustomerTest extends ModelTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        TestHelper::cleanUp();
    }

    public function tearDown(): void
    {
        TestHelper::cleanUp();
        parent::tearDown();
    }

    public function testCreateCustomer() {

        $customer = new Customer();
        $customer->setKey('foo');
        $customer->setPublished(true);
        $customer->setActive(true);
        $customer->setParentId(1);
        $customer->setFirstname('Peter');
        $customer->setLastname('Hugo');
        $customer->save();

        $this->assertGreaterThan(0, $customer->getId());
    }

}

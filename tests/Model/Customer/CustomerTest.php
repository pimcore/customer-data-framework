<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\Tests\Model\Customer;

use Pimcore\Model\DataObject\Customer;
use Pimcore\Tests\Support\Test\ModelTestCase;
use Pimcore\Tests\Support\Util\TestHelper;

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

    public function testCreateCustomer()
    {

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

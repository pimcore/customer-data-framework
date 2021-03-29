<?php

namespace CustomerManagementFrameworkBundle\Tests\Model\Activity;
use CustomerManagementFrameworkBundle\ActivityManager\ActivityManagerInterface;
use CustomerManagementFrameworkBundle\Model\Activity\GenericActivity;
use Pimcore\Model\DataObject\Customer;
use Pimcore\Tests\Test\ModelTestCase;
use Pimcore\Tests\Util\TestHelper;

class ActivityTest extends ModelTestCase
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

    protected function createCustomer(): Customer {
        $customer = new Customer();
        $customer->setKey(uniqid());
        $customer->setPublished(true);
        $customer->setActive(true);
        $customer->setParentId(1);
        $customer->setFirstname('Peter');
        $customer->setLastname('Hugo');
        $customer->save();
        return $customer;
    }

    public function testTrackActivity() {

        $activityManager = \Pimcore::getContainer()->get(ActivityManagerInterface::class);
        $activityStore = \Pimcore::getContainer()->get('cmf.activity_store');

        $customer = $this->createCustomer();


        $activity = new GenericActivity(['type' => 'test', 'attributes' => []]);
        $activity->setCustomer($customer);
        $activityManager->trackActivity($activity);
        $this->assertEquals(1, $activityStore->countActivitiesOfCustomer($customer));


        $activity = new GenericActivity(['type' => 'test2', 'attributes' => []]);
        $activity->setCustomer($customer);
        $activityManager->trackActivity($activity);
        $this->assertEquals(2, $activityStore->countActivitiesOfCustomer($customer));


        // --- create another customer
        $customer = $this->createCustomer();

        $activity = new GenericActivity(['type' => 'test2', 'attributes' => []]);
        $activity->setCustomer($customer);
        $activityManager->trackActivity($activity);
        $this->assertEquals(1, $activityStore->countActivitiesOfCustomer($customer));

        $listing = $activityStore->getActivityList();
        $this->assertEquals(3, $listing->count());

        $listing = $activityStore->getActivityList();
        $listing->setLimit(1);
        $this->assertEquals(1, count($listing->getActivities()));


        $listing = $activityStore->getActivityList();
        $listing->addConditionParam('`type` = ? OR `type` = ?', ['test2', 'dsfsdf']);
        $this->assertEquals(2, $listing->count());


        // --- create another customer
        $customer = $this->createCustomer();

        $activity = new GenericActivity(['type' => 'test2', 'attributes' => ['foo' => 'test', 'bar' => 'bla']]);
        $activity->setCustomer($customer);
        $activityManager->trackActivity($activity);
        $this->assertEquals(1, $activityStore->countActivitiesOfCustomer($customer));

    }

}

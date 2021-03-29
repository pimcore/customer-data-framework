<?php

namespace CustomerManagementFrameworkBundle\Tests\Model\Customer;


use Carbon\Carbon;
use CustomerManagementFrameworkBundle\CustomerList\Filter\CustomerSegment;
use CustomerManagementFrameworkBundle\CustomerList\Filter\SearchQuery;
use CustomerManagementFrameworkBundle\Listing\Filter\BoolCombinator;
use CustomerManagementFrameworkBundle\Listing\Filter\DateBetween;
use CustomerManagementFrameworkBundle\Listing\Filter\Equals;
use CustomerManagementFrameworkBundle\Listing\Filter\FloatBetween;
use CustomerManagementFrameworkBundle\Listing\Filter\Search;
use CustomerManagementFrameworkBundle\Listing\FilterHandler;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Pimcore\Model\DataObject\Customer;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Tests\Test\ModelTestCase;
use Pimcore\Tests\Util\TestHelper;

class CustomerListTest extends ModelTestCase
{

    protected $segmentDummyData = [
        ['name' => 'ga-s1', 'group' => 'ga'],
        ['name' => 'ga-s2', 'group' => 'ga'],
        ['name' => 'ga-s3', 'group' => 'ga'],
        ['name' => 'ga-s4', 'group' => 'ga'],
        ['name' => 'gb-s1', 'group' => 'gb'],
        ['name' => 'gb-s2', 'group' => 'gb'],
        ['name' => 'gb-s3', 'group' => 'gb']
    ];

    protected $customerDummyData = [
        [
            'firstname' => 'Peter',
            'lastname' => 'Hugo',
            'email' => 'peter.hugo@pimcore.fun',
            'active' => true,
            'published' => true,
            'zip' => 5000,
            'date' => '1980-11-05',
            'segments' => [
                'manual' => ['ga-s1', 'ga-s2', 'gb-s1'],
                'calculated' => ['gb-s2']
            ]
        ], [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => 'jane.doe@pimcore.fun',
            'active' => true,
            'published' => true,
            'zip' => 4000,
            'date' => '1980-01-01',
            'segments' => [
                'manual' => ['ga-s1', 'ga-s2', 'gb-s1'],
                'calculated' => []
            ]
        ], [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@pimcore.fun',
            'active' => true,
            'published' => true,
            'zip' => 4500,
            'date' => '1980-05-05',
            'segments' => [
                'manual' => ['ga-s3', 'ga-s4'],
                'calculated' => []
            ]
        ], [
            'firstname' => 'Sam',
            'lastname' => 'Jackman Pet',
            'email' => 'sam.jackman@pimcore.fun',
            'active' => true,
            'published' => true,
            'zip' => 4800,
            'date' => '1980-05-06',
            'segments' => [
                'manual' => ['ga-s3', 'ga-s4'],
                'calculated' => ['gb-s3']
            ]
        ], [
            'firstname' => 'Sohpie',
            'lastname' => 'Fischer Hugo',
            'email' => 'sophie.fischer@pimcore.fun',
            'active' => true,
            'published' => true,
            'zip' => 4999,
            'date' => '1980-06-05',
            'segments' => [
                'manual' => ['ga-s1', 'ga-s2', 'ga-s3', 'ga-s4'],
                'calculated' => []
            ]
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();
        TestHelper::cleanUp();

        $this->createSegments();
        $this->createCustomers();
    }

    public function tearDown(): void
    {
        TestHelper::cleanUp();
        parent::tearDown();
    }

    protected function createSegments()
    {
        $segmentManager = \Pimcore::getContainer()->get(SegmentManagerInterface::class);
        foreach ($this->segmentDummyData as $segment) {
            $segmentManager->createSegment($segment['name'], $segment['group'], $segment['name']);
        }
    }

    protected function createCustomers()
    {
        $segmentManager = \Pimcore::getContainer()->get(SegmentManagerInterface::class);

        foreach ($this->customerDummyData as &$customerData) {

            $customer = new Customer();
            $customer->setKey(uniqid());
            $customer->setPublished($customerData['published']);
            $customer->setActive($customerData['active']);
            $customer->setParentId(1);
            $customer->setFirstname($customerData['firstname']);
            $customer->setLastname($customerData['lastname']);
            $customer->setEmail($customerData['email']);
            $customer->setZip($customerData['zip']);
            $customer->setBirthdate(Carbon::createFromFormat('Y-m-d', $customerData['date']));

            $segments = [];
            foreach ($customerData['segments']['manual'] as $segmentReference) {
                $segments[] = $segmentManager->getSegmentByReference($segmentReference);
            }
            $customer->setManualSegments($segments);

            $segments = [];
            foreach ($customerData['segments']['calculated'] as $segmentReference) {
                $segments[] = new ObjectMetadata('calculatedSegments', [], $segmentManager->getSegmentByReference($segmentReference));
            }
            $customer->setCalculatedSegments($segments);

            $customer->save();

            $customerData['o_id'] = $customer->getId();
        }

    }

    public function testListAllCustomers()
    {
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $modifiedListing = $handler->getListing();

        $this->assertEquals(count($this->customerDummyData), $modifiedListing->getCount());
    }

    public function testEqualsFilter()
    {
        //test one filter
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $equalsFilter = new Equals('firstname', 'jane');
        $handler->addFilter($equalsFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(1, $modifiedListing->getCount());


        //test one filter
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $equalsFilter = new Equals('firstname', 'jane', true);
        $handler->addFilter($equalsFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(count($this->customerDummyData) - 1, $modifiedListing->getCount());


        //test two filters
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $equalsFilter = new Equals('firstname', 'jane');
        $handler->addFilter($equalsFilter);

        $equalsFilter = new Equals('lastname', 'doe');
        $handler->addFilter($equalsFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(1, $modifiedListing->getCount());

        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $equalsFilter = new Equals('firstname', 'jane');
        $handler->addFilter($equalsFilter);

        $equalsFilter = new Equals('firstname', 'doe');
        $handler->addFilter($equalsFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(0, $modifiedListing->getCount());


        //test filters array
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $equalsFilter = [];
        $equalsFilter[] = new Equals('firstname', 'jane');
        $equalsFilter[] = new Equals('lastname', 'doe');

        $handler->addFilters($equalsFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(1, $modifiedListing->getCount());

    }


    public function testFloatBetweenFilter()
    {
        //test one filter
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $betweenFilter = new FloatBetween('o_id', $this->customerDummyData[0]['o_id'], 10000);
        $handler->addFilter($betweenFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(count($this->customerDummyData), $modifiedListing->getCount());


        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $betweenFilter = new FloatBetween('o_id', $this->customerDummyData[0]['o_id'], $this->customerDummyData[1]['o_id']);
        $betweenFilter->setInclusive(true);
        $handler->addFilter($betweenFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(2, $modifiedListing->getCount());

        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $betweenFilter = new FloatBetween('o_id', $this->customerDummyData[0]['o_id'], $this->customerDummyData[1]['o_id']);
        $betweenFilter->setInclusive(false);
        $handler->addFilter($betweenFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(0, $modifiedListing->getCount());

        //test two filters
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $betweenFilter = new FloatBetween('o_id', $this->customerDummyData[0]['o_id'], $this->customerDummyData[1]['o_id']);
        $handler->addFilter($betweenFilter);

        $betweenFilter = new FloatBetween('o_id', $this->customerDummyData[0]['o_id'], $this->customerDummyData[3]['o_id']);
        $handler->addFilter($betweenFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(2, $modifiedListing->getCount());

        //TODO only makes sense with or filters ... do not work yet.
//        $listing = new Customer\Listing();
//        $handler = new FilterHandler($listing);
//
//        $betweenFilter = new FloatBetween('o_id', $this->customerDummyData[0]['o_id'], $this->customerDummyData[1]['o_id']);
//        $handler->addFilter($betweenFilter);
//
//        $betweenFilter = new FloatBetween('o_id', $this->customerDummyData[1]['o_id'], $this->customerDummyData[2]['o_id']);
//        $handler->addFilter($betweenFilter);
//
//        $modifiedListing = $handler->getListing();
//        $this->assertEquals(3, $modifiedListing->getCount());
//
//
//        //test filters array
//        $listing = new Customer\Listing();
//        $handler = new FilterHandler($listing);
//
//        $betweenFilter = [];
//        $betweenFilter[] = new FloatBetween('o_id', $this->customerDummyData[0]['o_id'], $this->customerDummyData[1]['o_id']);
//        $betweenFilter[] = new FloatBetween('o_id', $this->customerDummyData[1]['o_id'], $this->customerDummyData[2]['o_id']);
//
//        $handler->addFilters($betweenFilter);
//
//        $modifiedListing = $handler->getListing();
//        $this->assertEquals(3, $modifiedListing->getCount());

    }

    public function testDateBetweenFilter()
    {
        //test one filter
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $dateFilter = new DateBetween('birthdate', Carbon::createFromFormat('Y-m-d', '1970-01-01'), Carbon::now());
        $handler->addFilter($dateFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(count($this->customerDummyData), $modifiedListing->getCount());


        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $dateFilter = new DateBetween('birthdate', Carbon::createFromFormat('Y-m-d', '1980-05-01'), Carbon::createFromFormat('Y-m-d', '1980-05-31'));
        $handler->addFilter($dateFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(2, $modifiedListing->getCount());


        //test two filters
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $dateFilter = new DateBetween('birthdate', Carbon::createFromFormat('Y-m-d', '1980-05-01'), Carbon::createFromFormat('Y-m-d', '1980-05-31'));
        $handler->addFilter($dateFilter);

        $dateFilter = new DateBetween('birthdate', Carbon::createFromFormat('Y-m-d', '1980-01-01'), Carbon::createFromFormat('Y-m-d', '1980-08-31'));
        $handler->addFilter($dateFilter);


        $modifiedListing = $handler->getListing();
        $this->assertEquals(2, $modifiedListing->getCount());

        //TODO add or filters as soon as they work
    }

    public function testCustomerSegmentFilter()
    {
        $segmentManager = \Pimcore::getContainer()->get(SegmentManagerInterface::class);

        //test one filter
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $segments = [];
        $segments[] = $segmentManager->getSegmentByReference('ga-s1');

        $segmentFilter = new CustomerSegment($segments);
        $handler->addFilter($segmentFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(3, $modifiedListing->getCount());

        //test two segments
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $segments = [];
        $segments[] = $segmentManager->getSegmentByReference('ga-s1');
        $segments[] = $segmentManager->getSegmentByReference('ga-s2');

        $segmentFilter = new CustomerSegment($segments);
        $handler->addFilter($segmentFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(3, $modifiedListing->getCount());


        //test two segments - 2
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $segments = [];
        $segments[] = $segmentManager->getSegmentByReference('ga-s1');
        $segments[] = $segmentManager->getSegmentByReference('gb-s3');

        $segmentFilter = new CustomerSegment($segments);
        $handler->addFilter($segmentFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(0, $modifiedListing->getCount());

        //test two segments - with OR
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $segments = [];
        $segments[] = $segmentManager->getSegmentByReference('ga-s1');
        $segments[] = $segmentManager->getSegmentByReference('gb-s3');

        $segmentFilter = new CustomerSegment($segments, null, 'OR');
        $handler->addFilter($segmentFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(4, $modifiedListing->getCount());
    }


    public function testSearchFilter()
    {
        //test one filter
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter = new Search('firstname', 'jane');
        $handler->addFilter($searchFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(1, $modifiedListing->getCount());

        //test one filter
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter = new Search('firstname', 'jane', true);
        $handler->addFilter($searchFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(count($this->customerDummyData) - 1, $modifiedListing->getCount());

        //test one filter
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter = new Search('email', 'pimcore');
        $handler->addFilter($searchFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(count($this->customerDummyData), $modifiedListing->getCount());

    }

    public function testSingleSearchQueryFilter() {

        //test one filter
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter = new SearchQuery(['firstname'], 'jan*');
        $handler->addFilter($searchFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(1, $modifiedListing->getCount());

        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter = new SearchQuery(['firstname', 'lastname'], '*pet*');
        $handler->addFilter($searchFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(2, $modifiedListing->getCount());


        // ------------
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter = new SearchQuery(['firstname'], 'jan* OR sam*');
        $handler->addFilter($searchFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(2, $modifiedListing->getCount());

        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter = new SearchQuery(['firstname', 'lastname'], '*pet* OR joh*');
        $handler->addFilter($searchFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(3, $modifiedListing->getCount());

        // ------------
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter = new SearchQuery(['firstname'], 'jan* AND sam*');
        $handler->addFilter($searchFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(0, $modifiedListing->getCount());

        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter = new SearchQuery(['firstname', 'lastname'], '*pet* AND joh*');
        $handler->addFilter($searchFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(0, $modifiedListing->getCount());


        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter = new SearchQuery(['firstname', 'lastname'], '*pet* AND hug*');
        $handler->addFilter($searchFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(1, $modifiedListing->getCount());


        // ------------
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter = new SearchQuery(['email'], '*pimcore* AND !sam*');
        $handler->addFilter($searchFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(4, $modifiedListing->getCount());


        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter = new SearchQuery(['firstname', 'lastname', 'email'], '*pimcore* AND !sam*');
        $handler->addFilter($searchFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(4, $modifiedListing->getCount());



        // ------------
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter = new SearchQuery(['email'], '(*pimcore* AND !sam*) OR *fun');
        $handler->addFilter($searchFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(5, $modifiedListing->getCount());


        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter = new SearchQuery(['firstname', 'lastname', 'email'], '(*pimcore* AND !sam*) OR *jack*');
        $handler->addFilter($searchFilter);

        $modifiedListing = $handler->getListing();
        $this->assertEquals(5, $modifiedListing->getCount());

    }


    public function testBoolCombinatorFilter() {
        //test one filter
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $equalsFilter1 = new Equals('firstname', 'jane');
        $handler->addFilter(new BoolCombinator([$equalsFilter1], 'OR'));

        $modifiedListing = $handler->getListing();
        $this->assertEquals(1, $modifiedListing->getCount());


        //test two OR filters
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $equalsFilter1 = new Equals('firstname', 'jane');
        $equalsFilter2 = new Equals('firstname', 'john');
        $handler->addFilter(new BoolCombinator([$equalsFilter1, $equalsFilter2], 'OR'));

        $modifiedListing = $handler->getListing();
        $this->assertEquals(2, $modifiedListing->getCount());

        //test two AND filters
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $equalsFilter1 = new Equals('firstname', 'jane');
        $equalsFilter2 = new Equals('firstname', 'john');
        $handler->addFilter(new BoolCombinator([$equalsFilter1, $equalsFilter2], 'AND'));

        $modifiedListing = $handler->getListing();
        $this->assertEquals(0, $modifiedListing->getCount());

    }

    public function testCombinedSearchQueryFilter()
    {

        // --------------------
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter1 = new SearchQuery(['firstname'], 'jan*');
        $searchFilter2 = new SearchQuery(['firstname'], 'joh*');
        $handler->addFilter(new BoolCombinator([$searchFilter1, $searchFilter2], 'OR'));

        $modifiedListing = $handler->getListing();
        $this->assertEquals(2, $modifiedListing->getCount());

        // --------------------
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter1 = new SearchQuery(['firstname'], 'jan*');
        $searchFilter2 = new SearchQuery(['firstname'], 'joh*');
        $handler->addFilter(new BoolCombinator([$searchFilter1, $searchFilter2], 'AND'));

        $modifiedListing = $handler->getListing();
        $this->assertEquals(0, $modifiedListing->getCount());


        // --------------------
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter1 = new SearchQuery(['lastname'], '*hugo*');
        $searchFilter2 = new SearchQuery(['firstname'], 'pet*');
        $handler->addFilter(new BoolCombinator([$searchFilter1, $searchFilter2], 'OR'));


        $modifiedListing = $handler->getListing();
        $this->assertEquals(2, $modifiedListing->getCount());


        // --------------------
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter1 = new SearchQuery(['lastname'], '*hugo*');
        $searchFilter2 = new SearchQuery(['firstname'], 'pet*');
        $handler->addFilter(new BoolCombinator([$searchFilter1, $searchFilter2], 'AND'));

        $modifiedListing = $handler->getListing();
        $this->assertEquals(1, $modifiedListing->getCount());

        // --------------------
        $listing = new Customer\Listing();
        $handler = new FilterHandler($listing);

        $searchFilter1 = new SearchQuery(['lastname'], '*hugo*');
        $searchFilter2 = new SearchQuery(['firstname'], 'pet*');
        $handler->addFilter(new BoolCombinator([$searchFilter1, $searchFilter2], 'AND'));

        $modifiedListing = $handler->getListing();
        $this->assertEquals(1, $modifiedListing->getCount());

    }
}

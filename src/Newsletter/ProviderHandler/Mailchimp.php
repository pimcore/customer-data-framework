<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\SegmentExporter;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Model\Object\CustomerSegment;

class Mailchimp implements NewsletterProviderHandlerInterface
{
    use LoggerAware;

    /**
     * @var SegmentExporter
     */
    protected $segmentExporter;

    /**
     * @var SegmentManagerInterface
     */
    protected $segmentManager;

    public function __construct($listId, SegmentExporter $segmentExporter, SegmentManagerInterface $segmentManager)
    {
        $this->listId = $listId;
        $this->segmentExporter = $segmentExporter;
        $this->segmentManager = $segmentManager;
    }


    /**
     * @param null|string $subResource
     *
     * @return string
     */
    public function getListResourceUrl($subResource = null)
    {
        $url = sprintf('lists/%s', $this->listId);

        if ($subResource) {
            $url = sprintf('%s/%s', $url, $subResource);
        }

        return $url;
    }

    public function updateCustomer(CustomerInterface $customer)
    {
        // TODO: Implement updateCustomer() method.
    }

    public function updateCustomerEmail(CustomerInterface $customer, $oldEmail)
    {
        // TODO: Implement updateCustomerEmail() method.
    }

    public function deleteCustomer()
    {
        // TODO: Implement deleteCustomer() method.
    }

    public function updateSegmentGroups(array $groups)
    {
        $groupIds = [];

        foreach($groups as $group) {
            $remoteGroupId = $this->segmentExporter->exportGroup($group);

            $groupIds[] = $remoteGroupId;

            $segments = $this->segmentManager->getSegmentsFromSegmentGroup($group);

            $segmentIds = [];
            foreach($segments as $segment) {
                /**
                 * @var CustomerSegment $segment
                 */
                $segmentIds[] = $this->segmentExporter->exportSegment($segment, $remoteGroupId);
            }

            $this->segmentExporter->deleteNonExistingSegmentsFromGroup($segmentIds, $remoteGroupId);
        }


        $this->segmentExporter->deleteNonExistingGroups($groupIds);
    }

}
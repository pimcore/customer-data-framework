<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Model;

use CustomerManagementFrameworkBundle\Service\ObjectToArray;

abstract class AbstractCustomer extends \Pimcore\Model\Object\Concrete implements CustomerInterface
{
    public function cmfToArray()
    {
        $result = ObjectToArray::getInstance()->toArray($this);

        $segmentIds = [];
        foreach ($this->getAllSegments() as $segment) {
            $segmentIds[] = $segment->getId();
        }
        $result['segments'] = $segmentIds;

        unset($result['manualSegments']);
        unset($result['calculatedSegments']);

        return $result;
    }

    /**
     * @return CustomerSegmentInterface[]
     */
    public function getAllSegments()
    {
        return array_merge((array)$this->getCalculatedSegments(), (array)$this->getManualSegments());
    }

    public function getRelatedCustomerGroups()
    {
        return [];
    }
}

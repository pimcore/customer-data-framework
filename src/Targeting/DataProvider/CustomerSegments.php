<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Targeting\DataProvider;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Targeting\DataProvider\DataProviderInterface;
use Pimcore\Targeting\DataProviderDependentInterface;
use Pimcore\Targeting\Model\VisitorInfo;

class CustomerSegments implements DataProviderInterface, DataProviderDependentInterface
{
    const PROVIDER_KEY = 'cmf_customer_segments';

    /**
     * @inheritDoc
     */
    public function getDataProviderKeys(): array
    {
        return [Customer::PROVIDER_KEY];
    }

    /**
     * @inheritDoc
     */
    public function load(VisitorInfo $visitorInfo)
    {
        if ($visitorInfo->has(self::PROVIDER_KEY)) {
            return;
        }

        $visitorInfo->set(self::PROVIDER_KEY, $this->loadSegments($visitorInfo));
    }

    private function loadSegments(VisitorInfo $visitorInfo): array
    {
        /** @var CustomerInterface $customer */
        $customer = $visitorInfo->get(Customer::PROVIDER_KEY);
        if (!$customer) {
            return [];
        }

        $segments = [];
        $segments = $this->extractSegmentsFromPimcoreFieldData($customer->getManualSegments(), $segments);
        $segments = $this->extractSegmentsFromPimcoreFieldData($customer->getCalculatedSegments(), $segments);

        return $segments;
    }

    private function extractSegmentsFromPimcoreFieldData($field, array $segments = []): array
    {
        if (!is_array($field) || empty($field)) {
            return $segments;
        }

        foreach ($field as $segment) {
            $segmentId = null;
            $count     = 1;

            if ($segment instanceof CustomerSegmentInterface) {
                $segmentId = $segment->getId();
            } elseif ($segment instanceof ObjectMetadata && $segment->getObject() instanceof CustomerSegmentInterface) {
                $segmentId = $segment->getObject()->getId();

                $count = $segment->getApplication_counter();
                if (is_numeric($count)) {
                    $count = (int)$count;
                } else {
                    $count = 1;
                }
            }

            if (!isset($segments[$segmentId])) {
                $segments[$segmentId] = 0;
            }

            $segments[$segmentId] += $count;
        }

        return $segments;
    }
}

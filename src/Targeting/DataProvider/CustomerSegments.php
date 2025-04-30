<?php

declare(strict_types=1);

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\Targeting\DataProvider;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentExtractor\SegmentExtractorInterface;
use Pimcore\Bundle\PersonalizationBundle\Targeting\DataProvider\DataProviderInterface;
use Pimcore\Bundle\PersonalizationBundle\Targeting\DataProviderDependentInterface;
use Pimcore\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

class CustomerSegments implements DataProviderInterface, DataProviderDependentInterface
{
    const PROVIDER_KEY = 'cmf_customer_segments';

    /**
     * @var SegmentExtractorInterface
     */
    private $segmentExtractor;

    public function __construct(SegmentExtractorInterface $segmentExtractor)
    {
        $this->segmentExtractor = $segmentExtractor;
    }

    public function getDataProviderKeys(): array
    {
        return [Customer::PROVIDER_KEY];
    }

    public function load(VisitorInfo $visitorInfo): void
    {
        if ($visitorInfo->has(self::PROVIDER_KEY)) {
            return;
        }

        $visitorInfo->set(self::PROVIDER_KEY, $this->loadSegments($visitorInfo));
    }

    private function loadSegments(VisitorInfo $visitorInfo): array
    {
        /** @var CustomerInterface|null $customer */
        $customer = $visitorInfo->get(Customer::PROVIDER_KEY);
        if (!$customer) {
            return [];
        }

        return $this->segmentExtractor->getAllSegmentApplicationCounters($customer);
    }
}

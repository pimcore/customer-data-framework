<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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

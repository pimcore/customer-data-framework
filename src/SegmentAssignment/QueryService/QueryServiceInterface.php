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

namespace CustomerManagementFrameworkBundle\SegmentAssignment\QueryService;

use Pimcore\Model\Listing\AbstractListing;

/**
 * Interface for adding conditions to Pimcore\Model\Listing\AbstractListing
 * based on CustomerSegments assigned to elements in the result set
 *
 * @package CustomerManagementFrameworkBundle\SegmentAssignment\QueryServiceInterface
 */
interface QueryServiceInterface
{
    const MODE_CONJUNCTION = 'AND';

    const MODE_DISJUNCTION = 'OR';

    /**
     * adds a condition that narrows the result set down to elements that are assigned one or more/all of the passed $segmentIds
     * (based on $concatMode)
     *
     * @param string $concatMode
     *
     * @return mixed
     */
    public function bySegmentIds(AbstractListing $listing, array $segmentIds, $concatMode = self::MODE_DISJUNCTION);
}

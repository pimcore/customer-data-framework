<?php

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
     * @param AbstractListing $listing
     * @param array $segmentIds
     * @param string $concatMode
     *
     * @return mixed
     */
    public function bySegmentIds(AbstractListing $listing, array $segmentIds, $concatMode = self::MODE_DISJUNCTION);
}

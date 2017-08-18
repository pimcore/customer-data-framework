<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ActivityView;

use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Translate\TranslatorInterface;

interface ActivityViewInterface extends TranslatorInterface
{
    /**
     * @param ActivityStoreEntryInterface $activityEntry
     *
     * @return array|false
     */
    public function getOverviewAdditionalData(ActivityStoreEntryInterface $activityEntry);

    /**
     * @param ActivityStoreEntryInterface $activityEntry
     *
     * @return array
     */
    public function getDetailviewData(ActivityStoreEntryInterface $activityEntry);

    /**
     * @param ActivityStoreEntryInterface $activityEntry
     *
     * @return string|false
     */
    public function getDetailviewTemplate(ActivityStoreEntryInterface $activityEntry);

    /**
     * @param       $implementationClass
     * @param array $attributes
     * @param array $visibleKeys
     *
     * @return array
     */
    public function formatAttributes($implementationClass, array $attributes, array $visibleKeys = []);
}

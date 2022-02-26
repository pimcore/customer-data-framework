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

namespace CustomerManagementFrameworkBundle\ActivityView;

use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;

interface ActivityViewInterface
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
     * @param string $implementationClass
     * @param array $attributes
     * @param array $visibleKeys
     *
     * @return array
     */
    public function formatAttributes($implementationClass, array $attributes, array $visibleKeys = []);

    /**
     * Creates a link/button to an object, asset or document.
     *
     * @param int $id
     * @param string $elementType
     * @param string $buttonCssClass
     * @param string $buttonTranslationKey
     *
     * @return string
     */
    public function createPimcoreElementLink($id, $elementType, $buttonCssClass = 'btn btn-xs btn-default', $buttonTranslationKey = 'cmf_open');
}

<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
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

    /**
     * Creates a link/button to an object, asset or document.
     *
     * @param $id
     * @param $elementType
     * @param string $buttonCssClass
     * @param string $buttonTranslationKey
     * @return string
     */
    public function createPimcoreElementLink($id, $elementType, $buttonCssClass = 'btn btn-xs btn-default', $buttonTranslationKey = 'cmf_open');
}

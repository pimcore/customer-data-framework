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

namespace CustomerManagementFrameworkBundle\Model\Traits;

use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFrameworkBundle\CustomerSaveManager\SaveOptions;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\NewsletterProviderHandlerInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use CustomerManagementFrameworkBundle\Service\ObjectToArray;

trait CustomerTrait
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
        /**
         * @var SegmentManagerInterface $segmentManager
         */
        $segmentManager = \Pimcore::getContainer()->get('cmf.segment_manager');

        return array_merge(
            $segmentManager->getCalculatedSegmentsFromCustomer($this),
            $segmentManager->getManualSegmentsFromCustomer($this)
        );
    }

    public function getRelatedCustomerGroups()
    {
        return [];
    }

    /**
     * @param bool $disableVersions
     *
     * @return mixed
     */
    public function saveDirty($disableVersions = true)
    {
        return $this->getSaveManager()->saveDirty($this, $disableVersions);
    }

    /**
     * @param SaveOptions $saveOptions
     * @param bool $disableVersions
     *
     * @return mixed
     */
    public function saveWithOptions(SaveOptions $saveOptions, $disableVersions = false)
    {
        return $this->getSaveManager()->saveWithOptions($this, $saveOptions, $disableVersions);
    }

    /**
     * @return CustomerSaveManagerInterface
     */
    public function getSaveManager()
    {
        /**
         * @var CustomerSaveManagerInterface $saveManager
         */
        $saveManager = \Pimcore::getContainer()->get('cmf.customer_save_manager');

        return $saveManager;
    }

    /**
     * If this method returns true the customer will be exported by the provider handler with the given shortcut.
     * Otherwise the provider handler will delete the customer in the target system if it exists.
     *
     * @param NewsletterProviderHandlerInterface $newsletterProviderHandler
     *
     * @return bool
     */
    public function needsExportByNewsletterProviderHandler(NewsletterProviderHandlerInterface $newsletterProviderHandler)
    {
        return $this->getPublished() && $this->getActive();
    }
}

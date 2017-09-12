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

use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFrameworkBundle\CustomerSaveManager\SaveOptions;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\NewsletterProviderHandlerInterface;
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

    /**
     * @param bool $disableVersions
     * @return mixed
     */
    public function saveDirty($disableVersions = true)
    {
        return $this->getSaveManager()->saveDirty($this, $disableVersions);
    }

    /**
     * @param SaveOptions $saveOptions
     * @param bool $disableVersions
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
     * @return bool
     */
    public function needsExportByNewsletterProviderHandler(NewsletterProviderHandlerInterface $newsletterProviderHandler)
    {
        return $this->getPublished() && $this->getActive();
    }
}

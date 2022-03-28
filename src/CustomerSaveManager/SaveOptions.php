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

namespace CustomerManagementFrameworkBundle\CustomerSaveManager;

class SaveOptions
{
    /**
     * @var bool
     */
    private $onSaveSegmentBuildersEnabled;

    /**
     * @var bool
     */
    private $validatorEnabled;

    /**
     * @var bool
     */
    private $saveHandlersExecutionEnabled;

    /**
     * @var bool
     */
    private $segmentBuilderQueueEnabled;

    /**
     * @var bool
     */
    private $objectNamingSchemeEnabled;

    /**
     * @var bool
     */
    private $duplicatesIndexEnabled;

    /**
     * @var bool
     */
    private $newsletterQueueEnabled;

    /**
     * @var bool
     */
    private $newsletterQueueImmediateAsyncExecutionEnabled;

    /**
     * SaveOptions constructor.
     *
     * @param bool $onSaveSegmentBuildersEnabled
     * @param bool $validatorEnabled
     * @param bool $saveHandlersExecutionEnabled
     * @param bool $segmentBuilderQueueEnabled
     * @param bool $objectNamingSchemeEnabled
     * @param bool $duplicatesIndexEnabled
     * @param bool $newsletterQueueEnabled
     * @param bool $newsletterQueueImmediateAsyncExecutionEnabled
     */
    public function __construct(
        $onSaveSegmentBuildersEnabled = false,
        $validatorEnabled = false,
        $saveHandlersExecutionEnabled = false,
        $segmentBuilderQueueEnabled = false,
        $objectNamingSchemeEnabled = false,
        $duplicatesIndexEnabled = false,
        $newsletterQueueEnabled = false,
        $newsletterQueueImmediateAsyncExecutionEnabled = false
    ) {
        $this->onSaveSegmentBuildersEnabled = $onSaveSegmentBuildersEnabled;
        $this->validatorEnabled = $validatorEnabled;
        $this->saveHandlersExecutionEnabled = $saveHandlersExecutionEnabled;
        $this->segmentBuilderQueueEnabled = $segmentBuilderQueueEnabled;
        $this->objectNamingSchemeEnabled = $objectNamingSchemeEnabled;
        $this->duplicatesIndexEnabled = $duplicatesIndexEnabled;
        $this->newsletterQueueEnabled = $newsletterQueueEnabled;
        $this->newsletterQueueImmediateAsyncExecutionEnabled = $newsletterQueueImmediateAsyncExecutionEnabled;
    }

    /**
     * @return bool
     */
    public function isOnSaveSegmentBuildersEnabled()
    {
        return $this->onSaveSegmentBuildersEnabled;
    }

    /**
     * @return bool
     */
    public function isValidatorEnabled()
    {
        return $this->validatorEnabled;
    }

    /**
     * @return bool
     */
    public function isSaveHandlersExecutionEnabled()
    {
        return $this->saveHandlersExecutionEnabled;
    }

    /**
     * @return bool
     */
    public function isSegmentBuilderQueueEnabled()
    {
        return $this->segmentBuilderQueueEnabled;
    }

    /**
     * @return bool
     */
    public function isObjectNamingSchemeEnabled()
    {
        return $this->objectNamingSchemeEnabled;
    }

    /**
     * @return bool
     */
    public function isDuplicatesIndexEnabled()
    {
        return $this->duplicatesIndexEnabled;
    }

    /**
     * @return bool
     */
    public function isNewsletterQueueEnabled()
    {
        return $this->newsletterQueueEnabled;
    }

    /**
     * @return bool
     */
    public function isNewsletterQueueImmediateAsyncExecutionEnabled()
    {
        return $this->newsletterQueueImmediateAsyncExecutionEnabled;
    }

    /**
     * @return $this
     */
    public function disableOnSaveSegmentBuilders()
    {
        $this->onSaveSegmentBuildersEnabled = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function enableOnSaveSegmentBuilders()
    {
        $this->onSaveSegmentBuildersEnabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableValidator()
    {
        $this->validatorEnabled = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function enableValidator()
    {
        $this->validatorEnabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableSaveHandlers()
    {
        $this->saveHandlersExecutionEnabled = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function enableSaveHandlers()
    {
        $this->saveHandlersExecutionEnabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableSegmentBuilderQueue()
    {
        $this->segmentBuilderQueueEnabled = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function enableSegmentBuilderQueue()
    {
        $this->segmentBuilderQueueEnabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableObjectNamingScheme()
    {
        $this->objectNamingSchemeEnabled = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableDuplicatesIndex()
    {
        $this->duplicatesIndexEnabled = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function enableDuplicatesIndex()
    {
        $this->duplicatesIndexEnabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function enableObjectNamingScheme()
    {
        $this->objectNamingSchemeEnabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableNewsletterQueue()
    {
        $this->newsletterQueueEnabled = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function enableNewsletterQueue()
    {
        $this->newsletterQueueEnabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableNewsletterQueueImmediateAsyncExecution()
    {
        $this->newsletterQueueImmediateAsyncExecutionEnabled = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function enableNewsletterQueueImmediateAsyncExecution()
    {
        $this->newsletterQueueImmediateAsyncExecutionEnabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableAll()
    {
        $this
            ->disableDuplicatesIndex()
            ->disableNewsletterQueue()
            ->disableObjectNamingScheme()
            ->disableOnSaveSegmentBuilders()
            ->disableSaveHandlers()
            ->disableSegmentBuilderQueue()
            ->disableValidator();

        return $this;
    }
}

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

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler;

use CustomerManagementFrameworkBundle\ActivityManager\ActivityManagerInterface;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\DataTransformer\Cleanup\Email;
use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;
use CustomerManagementFrameworkBundle\DataValidator\EmailValidator;
use CustomerManagementFrameworkBundle\Model\Activity\MailchimpStatusChangeActivity;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\MailchimpAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Model\NewsletterAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\CustomerExporter\BatchExporter;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\CustomerExporter\SingleExporter;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\DataTransformer\MailchimpDataTransformerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\MailChimpExportService;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\SegmentExporter;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\WebhookProcessor;
use CustomerManagementFrameworkBundle\Newsletter\Queue\Item\DefaultNewsletterQueueItem;
use CustomerManagementFrameworkBundle\Newsletter\Queue\Item\NewsletterQueueItemInterface;
use CustomerManagementFrameworkBundle\Newsletter\Queue\NewsletterQueueInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\File;
use Pimcore\Model\DataObject\CustomerSegment;
use Pimcore\Model\DataObject\Service;
use Psr\Log\LoggerInterface;

class Mailchimp implements NewsletterProviderHandlerInterface
{
    use LoggerAware;

    const STATUS_SUBSCRIBED = 'subscribed';
    const STATUS_UNSUBSCRIBED = 'unsubscribed';
    const STATUS_PENDING = 'pending';
    const STATUS_CLEANED = 'cleaned';

    /**
     * @var string
     */
    protected $shortcut;

    /**
     * @var string
     */
    protected $listId;

    /**
     * @var array
     */
    protected $statusMapping;

    /**
     * @var array
     */
    protected $reverseStatusMapping;

    /**
     * @var array
     */
    protected $mergeFieldMapping;

    /**
     * @var MailchimpDataTransformerInterface[]
     */
    protected $fieldTransformers;

    /**
     * @var DataTransformerInterface[]
     */
    protected $reverseFieldTransformers;

    /**
     * @var SegmentExporter
     */
    protected $segmentExporter;

    /**
     * @var SegmentManagerInterface
     */
    protected $segmentManager;

    /**
     * @var MailChimpExportService
     */
    protected $exportService;

    /**
     * @var int
     */
    protected $batchThreshold = 50;

    /**
     * Mailchimp constructor.
     *
     * @param string $shortcut
     * @param string $listId
     * @param array $statusMapping
     * @param array $reverseStatusMapping
     * @param array $mergeFieldMapping
     * @param MailchimpDataTransformerInterface[] $fieldTransformers
     * @param SegmentExporter $segmentExporter
     * @param SegmentManagerInterface $segmentManager
     * @param MailChimpExportService $exportService
     *
     * @throws \Exception
     */
    public function __construct(
        $shortcut,
        $listId,
        array $statusMapping,
        array $reverseStatusMapping,
        array $mergeFieldMapping,
        array $fieldTransformers,
        SegmentExporter $segmentExporter,
        SegmentManagerInterface $segmentManager,
        MailChimpExportService $exportService
    ) {
        if (!strlen($shortcut) || !File::getValidFilename($shortcut)) {
            throw new \Exception('Please provide a valid newsletter provider handler shortcut.');
        }
        $this->shortcut = $shortcut;
        $this->listId = $listId;
        $this->statusMapping = $statusMapping;
        $this->reverseStatusMapping = $reverseStatusMapping;
        $this->mergeFieldMapping = $mergeFieldMapping;
        $this->fieldTransformers = $fieldTransformers;
        $this->segmentExporter = $segmentExporter;
        $this->segmentManager = $segmentManager;
        $this->exportService = $exportService;
    }

    /**
     * update customer in mail provider
     *
     * @param NewsletterQueueItemInterface[] $items
     * @param bool $forceUpdate
     *
     * @return void
     */
    public function processCustomerQueueItems(array $items, $forceUpdate = false)
    {
        $items = $this->getUpdateNeededItems($items, $forceUpdate);

        list($emailChangedItems, $regularItems) = $this->determineEmailChangedItems($items);

        //Customers where the email address changed need to be handled by the single exporter as the batch exporter does not allow such operations.
        if (sizeof($emailChangedItems)) {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][%s] process %s items where the email address changed...',
                    $this->getShortcut(),
                    sizeof($emailChangedItems)
                )
            );

            foreach ($emailChangedItems as $item) {
                $this->customerExportSingle($item);
            }
        }

        $itemCount = count($regularItems);

        if (!$itemCount) {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][%s] 0 items to process...',
                    $this->getShortcut()
                )
            );
        } elseif ($itemCount <= $this->batchThreshold) {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][%s] Data count (%d) is below batch threshold (%d), sending one request per entry...',
                    $this->getShortcut(),
                    $itemCount,
                    $this->batchThreshold
                )
            );
            foreach ($regularItems as $item) {
                $this->customerExportSingle($item);
            }
        } else {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][%s] Sending data as batch request',
                    $this->getShortcut()
                )
            );
            $this->customerExportBatch($regularItems);
        }
    }

    /**
     * @param NewsletterQueueItemInterface[] $items
     *
     * @return array
     */
    protected function determineEmailChangedItems(array $items)
    {
        $emailChangedItems = [];
        $regularItems = [];

        foreach ($items as $item) {
            if ($item->getOperation() != NewsletterQueueInterface::OPERATION_UPDATE) {
                $regularItems[] = $item;
                continue;
            }

            if (!$item->getCustomer()) {
                $regularItems[] = $item;
                continue;
            }

            if ($item->getCustomer()->getEmail() != $item->getEmail()) {
                $emailChangedItems[] = $item;
                continue;
            }

            $regularItems[] = $item;
        }

        return [$emailChangedItems, $regularItems];
    }

    /**
     * @param NewsletterQueueItemInterface[] $items
     * @param bool $forceUpdate
     *
     * @return NewsletterQueueItemInterface[]
     */
    protected function getUpdateNeededItems(array $items, $forceUpdate = false)
    {
        $updateNeededItems = [];
        foreach ($items as $item) {
            $emailValidator = new EmailValidator();
            /** @var MailchimpAwareCustomerInterface|null $customer */
            $customer = $item->getCustomer();

            if ($customer && !$emailValidator->isValid($customer->getEmail()) && !$emailValidator->isValid(!$item->getEmail())) {
                $this->getLogger()->info(
                    sprintf(
                        '[MailChimp][CUSTOMER %s][%s] Export not needed as the customer has no valid email address.',
                        $customer->getId(),
                        $this->getShortcut()
                    )
                );

                $item->setSuccessfullyProcessed(true);
            } elseif (!$customer) {
                $updateNeededItems[] = $item;
            } elseif ($item->getOperation() == NewsletterQueueInterface::OPERATION_UPDATE) {
                if (!$customer->needsExportByNewsletterProviderHandler($this)) {
                    /* Update item only if a mailchimp status is set in the customer.
                       Otherwise the customer should not exist in the mailchimp list and therefore no deletion should be needed.
                       Cleaned customers will be ignored as the email adress is invalid
                    */

                    $mailchimpStatus = $this->getMailchimpStatus($customer);

                    if ($mailchimpStatus && ($mailchimpStatus != self::STATUS_CLEANED)) {
                        $updateNeededItems[] = $item;
                    } else {
                        $this->getLogger()->info(
                            sprintf(
                                '[MailChimp][CUSTOMER %s][%s] Export not needed as the export data did not change (customer is not in export list).',
                                $customer->getId(),
                                $this->getShortcut()
                            )
                        );

                        $item->setSuccessfullyProcessed(true);
                    }
                } elseif ($forceUpdate || $this->exportService->didExportDataChangeSinceLastExport($customer, $this->getListId(), $this->buildEntry($customer))) {
                    $mailchimpStatus = $this->getMailchimpStatus($customer);
                    if (!$mailchimpStatus) {
                        $entry = $this->buildEntry($customer);

                        $setStatus = isset($entry['status_if_new']) ?: $entry['status'];

                        if ($setStatus == self::STATUS_UNSUBSCRIBED) {
                            $this->getLogger()->info(
                                sprintf(
                                    '[MailChimp][CUSTOMER %s][%s] Export not needed as the customer is unsubscribed and was not exported yet.',
                                    $customer->getId(),
                                    $this->getShortcut()
                                )
                            );
                            $item->setSuccessfullyProcessed(true);
                        } else {
                            $updateNeededItems[] = $item;
                        }
                    } else {
                        $updateNeededItems[] = $item;
                    }
                } else {
                    $this->getLogger()->info(
                        sprintf(
                            '[MailChimp][CUSTOMER %s][%s] Export not needed as the export data did not change.',
                            $customer->getId(),
                            $this->getShortcut()
                        )
                    );

                    $item->setSuccessfullyProcessed(true);
                }
            } else {
                $updateNeededItems[] = $item;
            }
        }

        return $updateNeededItems;
    }

    /**
     * Fetches customer data via the Mailchimp API.
     *
     * @param NewsletterAwareCustomerInterface&MailchimpAwareCustomerInterface $customer
     *
     * @return array|null
     */
    public function fetchCustomer(NewsletterAwareCustomerInterface $customer)
    {
        return $this->getSingleExporter()->fetchCustomer($this, $customer);
    }

    /**
     * Directly Subscribes/exports a customer with mailchimp status "subscribed" via the Mailchimp API.
     *
     * @param NewsletterAwareCustomerInterface $customer
     *
     * @return bool success
     */
    public function subscribeCustomer(NewsletterAwareCustomerInterface $customer)
    {
        return $this->subscribeCustomerWithStatus($customer, self::STATUS_SUBSCRIBED);
    }

    /**
     * Directly Subscribes/exports a customer with mailchimp status "pending" via the Mailchimp API.
     *
     * @param NewsletterAwareCustomerInterface $customer
     *
     * @return bool success
     */
    public function subscribeCustomerPending(NewsletterAwareCustomerInterface $customer)
    {
        return $this->subscribeCustomerWithStatus($customer, self::STATUS_PENDING);
    }

    /**
     * Directly Subscribes/exports a customer with given mailchimp status "subscribed" via the Mailchimp API.
     *
     * @param NewsletterAwareCustomerInterface $customer
     *
     * @return bool success
     */
    public function subscribeCustomerWithStatus(NewsletterAwareCustomerInterface $customer, string $status)
    {
        /**
         * @var MailchimpAwareCustomerInterface $customer;
         */
        if (!$newsletterStatus = $this->reverseMapNewsletterStatus($status)) {
            $this->getLogger()->error(sprintf('subscribe failed: could not reverse map mailchimp status %s', $status));

            return false;
        }
        try {
            $this->setNewsletterStatus($customer, $newsletterStatus);

            $item = new DefaultNewsletterQueueItem(
                $customer->getId(),
                $customer,
                $customer->getEmail(),
                NewsletterQueueInterface::OPERATION_UPDATE
            );

            $success = $this->getSingleExporter()->update($customer, $item, $this);

            if ($success) {
                $customer->saveWithOptions(
                    $customer->getSaveManager()->getSaveOptions()
                        ->disableNewsletterQueue()
                        ->disableDuplicatesIndex()
                        ->disableOnSaveSegmentBuilders()
                );
            }
        } catch (\Exception $e) {
            $this->getLogger()->error('subscribe customer failed: '.$e->getMessage());

            return false;
        }

        return $success;
    }

    public function unsubscribeCustomer(NewsletterAwareCustomerInterface $customer)
    {
        /**
         * @var MailchimpAwareCustomerInterface $customer;
         */
        if (!$newsletterStatus = $this->reverseMapNewsletterStatus(self::STATUS_UNSUBSCRIBED)) {
            $this->getLogger()->error(sprintf('subscribe failed: could not reverse map mailchimp status %s', self::STATUS_UNSUBSCRIBED));

            return false;
        }

        try {
            $this->setNewsletterStatus($customer, $newsletterStatus);

            $item = new DefaultNewsletterQueueItem(
                $customer->getId(),
                $customer,
                $customer->getEmail(),
                NewsletterQueueInterface::OPERATION_UPDATE
            );

            $success = $this->getSingleExporter()->update($customer, $item, $this);

            if ($success) {
                $customer->saveWithOptions(
                    $customer->getSaveManager()->getSaveOptions()
                        ->disableNewsletterQueue()
                        ->disableDuplicatesIndex()
                        ->disableOnSaveSegmentBuilders()
                );
            }
        } catch (\Exception $e) {
            $this->getLogger()->error('unsubscribe customer failed: '.$e->getMessage());

            return false;
        }

        return $success;
    }

    public function updateSegmentGroups($forceUpdate = false)
    {
        $groups = $this->getExportableSegmentGroups();

        $groupIds = [];
        foreach ($groups as $group) {
            $remoteGroupId = $this->segmentExporter->exportGroup($group, $this, false, $forceUpdate);

            $groupIds[] = $remoteGroupId;

            $segments = $this->segmentManager->getSegmentsFromSegmentGroup($group);

            $segmentIds = [];
            foreach ($segments as $segment) {
                $forceCreate = false;
                if ($remoteGroupId && ($this->segmentExporter->getLastCreatedGroupRemoteId() == $remoteGroupId)) {
                    $forceCreate = true;
                }

                /**
                 * @var CustomerSegment $segment
                 */
                $segmentIds[] = $this->segmentExporter->exportSegment($segment, $this, $remoteGroupId, $forceCreate, $forceUpdate);
            }

            $this->segmentExporter->deleteNonExistingSegmentsFromGroup($segmentIds, $this, $remoteGroupId);
        }

        $this->segmentExporter->deleteNonExistingGroups($groupIds, $this);
    }

    protected function getExportableSegmentGroups()
    {
        $fieldname = 'exportNewsletterProvider' . ucfirst($this->getShortcut());

        $groups = $this->segmentManager->getSegmentGroups();
        $groups->addConditionParam($fieldname . ' = 1');

        return $groups;
    }

    protected function getAllExportableSegments()
    {
        $groups = $this->getExportableSegmentGroups();
        $idField = Service::getVersionDependentDatabaseColumnName('id');
        $select = $groups->getQueryBuilder()
            ->resetQueryPart('select')
            ->select($idField);

        $segments = $this->segmentManager->getSegments();
        $segments->addConditionParam('group__id in (' . $select . ')');

        return $segments;
    }

    protected function customerExportSingle(NewsletterQueueItemInterface $item)
    {
        $this->getSingleExporter()->export($item, $this);
    }

    /**
     * @param NewsletterQueueItemInterface[] $items
     */
    protected function customerExportBatch(array $items)
    {
        $this->getBatchExporter()->export($items, $this);
    }

    /**
     * @return SingleExporter
     */
    protected function getSingleExporter()
    {
        /**
         * @var SingleExporter $singleExporter
         */
        $singleExporter = \Pimcore::getContainer()->get(SingleExporter::class);

        return $singleExporter;
    }

    /**
     * @return BatchExporter
     */
    protected function getBatchExporter()
    {
        /**
         * @var BatchExporter $batchExporter
         */
        $batchExporter = \Pimcore::getContainer()->get(BatchExporter::class);

        return $batchExporter;
    }

    public function getListId()
    {
        return $this->listId;
    }

    /**
     * @return string
     */
    public function getShortcut()
    {
        return $this->shortcut;
    }

    public function buildEntry(MailchimpAwareCustomerInterface $customer)
    {
        $mergeFieldsMapping = sizeof($this->mergeFieldMapping) ? $this->mergeFieldMapping : [
            'firstname' => 'FNAME',
            'lastname' => 'LNAME'
        ];

        $mergeFields = [];
        foreach (array_keys($mergeFieldsMapping) as $field) {
            $mapping = $this->mapMergeField($field, $customer);

            // Check if this is a multi-value field e.g. ADDRESS and needs
            // merging itself.
            if (isset($mergeFields[$mapping['field']]) && is_array($mergeFields[$mapping['field']])) {
                $mergeFields[$mapping['field']] += $mapping['value'];
            } else {
                $mergeFields[$mapping['field']] = $mapping['value'];
            }
        }

        $emailCleaner = new Email();

        $result = [
            'email_address' => $emailCleaner->transform($customer->getEmail()),
            'merge_fields' => $mergeFields
        ];

        if ($language = $customer->getCustomerLanguage()) {
            $entry['language'] = $language;
        }

        if ($interests = $this->buildCustomerSegmentData($customer)) {
            $result['interests'] = $interests;
        }

        $result = $this->addNewsletterStatusToEntry($customer, $result);

        return $result;
    }

    /**
     * @param MailchimpAwareCustomerInterface $customer
     *
     * @return array
     */
    protected function buildCustomerSegmentData(MailchimpAwareCustomerInterface $customer)
    {
        $data = [];
        $customerSegments = [];
        foreach ($customer->getAllSegments() as $customerSegment) {
            $customerSegments[$customerSegment->getId()] = $customerSegment;
        }

        // Mailchimp's API only handles interests which are passed in the request and merges them with existing ones. Therefore
        // we need to pass ALL segments we know and set segments which are not set on the customer as false. Segments
        // which are not set on the customer, but were set before (and are set on Mailchimp's member record) will be kept set
        // if we don't explicitely set them to false.
        foreach ($this->getAllExportableSegments() as $segment) {
            $remoteSegmentId = $this->exportService->getRemoteId($segment, $this->listId);

            if (!$remoteSegmentId) {
                continue;
            }

            if (isset($customerSegments[$segment->getId()])) {
                $data[$remoteSegmentId] = true;
            } else {
                $data[$remoteSegmentId] = false;
            }
        }

        return sizeof($data) ? $data : null;
    }

    public function updateMailchimpStatus(MailchimpAwareCustomerInterface $customer, $status, $saveCustomer = true)
    {
        $getter = 'getMailchimpStatus' . ucfirst($this->getShortcut());

        // status did not changed => no customer save needed
        if ($customer->$getter() == $status) {
            return;
        }

        $this->setMailchimpStatus($customer, $status);

        $this->trackStatusChangeActivity($customer, $status);

        if ($saveCustomer) {
            /* The newsletter queue needs to be disabled to avoid endless loops.
               Some other components are disabled for performance reasons as they are not needed here.
               If somebody ever wants to build segments based on the mailchimp status then they could be handled via the segment building queue.
             */
            $customer->saveWithOptions(
                $customer->getSaveManager()->getSaveOptions(true)
                    ->disableNewsletterQueue()
                    ->disableOnSaveSegmentBuilders()
                    ->disableValidator()
                    ->disableDuplicatesIndex()
            );
        }
    }

    protected function trackStatusChangeActivity(MailchimpAwareCustomerInterface $customer, $status)
    {
        $activity = new MailchimpStatusChangeActivity($customer, $status, ['listId' => $this->getListId(), 'shortcut' => $this->getShortcut()]);
        /**
         * @var ActivityManagerInterface $activityManager
         */
        $activityManager = \Pimcore::getContainer()->get('cmf.activity_manager');
        $activityManager->trackActivity($activity);
    }

    public function setMailchimpStatus(MailchimpAwareCustomerInterface $customer, $status)
    {
        $setter = 'setMailchimpStatus' . ucfirst($this->getShortcut());
        if (!method_exists($customer, $setter)) {
            throw new \Exception(sprintf(
                'Customer needs to have a field %s in order to be able to hold the mailchimp status for newsletter provider handler with shortcut %s',
                $setter,
                $this->getShortcut()
            ));
        }

        $customer->$setter($status);
    }

    public function getMailchimpStatus(MailchimpAwareCustomerInterface $customer)
    {
        $getter = 'getMailchimpStatus' . ucfirst($this->getShortcut());

        if (!method_exists($customer, $getter)) {
            throw new \Exception(sprintf(
                'Customer needs to have a field %s in order to be able to hold the mailchimp status for newsletter provider handler with shortcut %s',
                $getter,
                $this->getShortcut()
            ));
        }

        return $customer->$getter();
    }

    public function setNewsletterStatus(MailchimpAwareCustomerInterface $customer, $status)
    {
        $setter = 'setNewsletterStatus' . ucfirst($this->getShortcut());
        if (!method_exists($customer, $setter)) {
            throw new \Exception(sprintf(
                'Customer needs to have a field %s in order to be able to hold the newsletter status for newsletter provider handler with shortcut %s',
                $setter,
                $this->getShortcut()
            ));
        }

        $customer->$setter($status);
    }

    public function getNewsletterStatus(MailchimpAwareCustomerInterface $customer)
    {
        $getter = 'getNewsletterStatus' . ucfirst($this->getShortcut());

        if (!method_exists($customer, $getter)) {
            throw new \Exception(sprintf(
                'Customer needs to have a field %s in order to be able to hold the newsletter status for newsletter provider handler with shortcut %s',
                $getter,
                $this->getShortcut()
            ));
        }

        return $customer->$getter();
    }

    public function processWebhook(array $webhookData, LoggerInterface $logger)
    {
        if ($webhookData['data']['list_id'] == $this->getListId()) {
            /**
             * @var WebhookProcessor $webhookProcesor
             */
            $webhookProcesor = \Pimcore::getContainer()->get(WebhookProcessor::class);
            $webhookProcesor->process($this, $webhookData, $logger);
        }
    }

    /**
     * Maps Pimcore class field newsletterStatus to mailchimpNewsletterStatus
     */
    protected function addNewsletterStatusToEntry(MailchimpAwareCustomerInterface $customer, array $entry)
    {
        $status = $this->getNewsletterStatus($customer);

        if (!isset($this->statusMapping[$status])) {
            $status = self::STATUS_UNSUBSCRIBED;
        } else {
            $status = $this->statusMapping[$status];
        }

        // if we do have a mailchimp status we should not update it
        if ($this->getMailchimpStatus($customer) == self::STATUS_CLEANED) {
            $status = self::STATUS_CLEANED;
        }

        if (!$customer->needsExportByNewsletterProviderHandler($this)) {
            $status = null;
        }

        if ($status != $this->getMailchimpStatus($customer)) {
            $entry['status'] = $status;
        } else {
            $entry['status_if_new'] = $status;
        }

        return $entry;
    }

    /**
     * Map mailchimp status to pimcore object newsletterStatus
     *
     * @param string $mailchimpStatus
     *
     * @return string|null
     */
    public function reverseMapNewsletterStatus($mailchimpStatus)
    {
        if (isset($this->reverseStatusMapping[$mailchimpStatus])) {
            return $this->reverseStatusMapping[$mailchimpStatus];
        }

        return null;
    }

    /**
     * @param string $field
     * @param MailchimpAwareCustomerInterface $customer
     *
     * @return array|null
     */
    public function mapMergeField($field, MailchimpAwareCustomerInterface $customer)
    {
        $getter = 'get' . ucfirst($field);
        $value = $customer->$getter();

        if (isset($this->mergeFieldMapping[$field])) {
            $to = $this->mergeFieldMapping[$field];

            if (isset($this->fieldTransformers[$field])) {
                $transformer = $this->fieldTransformers[$field];
                $value = $transformer->transformFromPimcoreToMailchimp($value);
            }

            $value = is_null($value) ? '' : $value;

            return ['field' => $to, 'value' => $value];
        }

        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     *
     * @return array|null
     */
    public function reverseMapMergeField($field, $value)
    {
        foreach ($this->mergeFieldMapping as $from => $to) {
            if ($to == $field) {
                if (isset($this->fieldTransformers[$from])) {
                    $transformer = $this->fieldTransformers[$from];
                    $value = $transformer->transformFromMailchimpToPimcore($value);
                }

                return ['field' => $from, 'value' => $value];
            }
        }

        return null;
    }

    /**
     * @param string $pimcoreField
     * @param mixed $pimcoreData
     * @param mixed $mailchimpImportData
     */
    public function didMergeFieldDataChange($pimcoreField, $pimcoreData, $mailchimpImportData)
    {
        if (!isset($this->fieldTransformers[$pimcoreField])) {
            return $pimcoreData != $mailchimpImportData;
        }

        return $this->fieldTransformers[$pimcoreField]->didMergeFieldDataChange($pimcoreData, $mailchimpImportData);
    }

    /**
     * @param string $email
     * @param int|null $customerId
     *
     * @return bool
     */
    public function doesOtherSubscribedCustomerWithEmailExist($email, $customerId = null)
    {
        if (!$email) {
            return false;
        }

        $customerProvider = $this->getCustomerProvider();

        $list = $customerProvider->getList();
        $customerProvider->addActiveCondition($list);
        $idField = Service::getVersionDependentDatabaseColumnName('id');
        if ($customerId) {
            $list->setCondition('trim(lower(email)) = ? and ' . $idField .' != ?', [trim(strtolower($email)), $customerId]);
        } else {
            $list->setCondition('trim(lower(email)) = ?', [trim(strtolower($email))]);
        }

        /** @var MailchimpAwareCustomerInterface $_customer */
        foreach ($list as $_customer) {
            if (in_array($this->getMailchimpStatus($_customer), [self::STATUS_PENDING, self::STATUS_SUBSCRIBED])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Override this method if you have multiple tenants
     *
     * @param string $email
     *
     * @return CustomerInterface|null
     */
    public function getActiveCustomerByEmail($email)
    {
        $customer = $this->getCustomerProvider()->getActiveCustomerByEmail($email);

        return $customer;
    }

    /**
     * @return MailChimpExportService
     */
    public function getExportService(): MailChimpExportService
    {
        return $this->exportService;
    }

    protected function getCustomerProvider(): CustomerProviderInterface
    {
        /**
         * @var CustomerProviderInterface $customerProvider
         */
        $customerProvider = \Pimcore::getContainer()->get(CustomerProviderInterface::class);

        return $customerProvider;
    }
}

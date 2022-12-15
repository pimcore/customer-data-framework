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

namespace CustomerManagementFrameworkBundle\Newsletter\Queue;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\NewsletterAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\NewsletterProviderHandlerInterface;
use CustomerManagementFrameworkBundle\Newsletter\Queue\Item\DefaultNewsletterQueueItem;
use CustomerManagementFrameworkBundle\Newsletter\Queue\Item\NewsletterQueueItemInterface;
use CustomerManagementFrameworkBundle\Traits\ApplicationLoggerAware;
use Knp\Component\Pager\PaginatorInterface;
use Pimcore\Db;
use Pimcore\Model\DataObject\Service;
use Pimcore\Tool\Console;

class DefaultNewsletterQueue implements NewsletterQueueInterface
{
    use ApplicationLoggerAware;

    const QUEUE_TABLE = 'plugin_cmf_newsletter_queue';

    protected $maxItemsPerRound;

    /**
     * @var NewsletterQueueItemInterface[]
     */
    private $immidateAsyncQueueItems = [];

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    public function __construct(PaginatorInterface $paginator, $maxItemsPerRound = 500)
    {
        $this->maxItemsPerRound = $maxItemsPerRound;
        $this->paginator = $paginator;
        $this->setLoggerComponent('NewsletterSync');
    }

    /**
     * @param NewsletterAwareCustomerInterface $customer
     * @param string $operation
     * @param string|null $email
     * @param bool $immediateAsyncProcessQueueItem
     */
    public function enqueueCustomer(NewsletterAwareCustomerInterface $customer, $operation, $email = null, $immediateAsyncProcessQueueItem = false)
    {
        $modificationDate = round(microtime(true) * 1000);
        $email = !is_null($email) ? $email : $customer->getEmail();

        $db = Db::get();
        $db->executeQuery(
            'insert into ' . self::QUEUE_TABLE . ' (customerId, email, operation, modificationDate) values (:customerId,:email,:operation,:modificationDate) on duplicate key update operation = :operation, modificationDate = :modificationDate',
            [
                'customerId' => $customer->getId(),
                'email' => $email,
                'operation' => $operation,
                'modificationDate' => $modificationDate,
            ]
        );

        if ($immediateAsyncProcessQueueItem) {
            $this->addImmidiateAsyncQueueItem($customer, $email, $operation, $modificationDate);
        }
    }

    private function addImmidiateAsyncQueueItem(NewsletterAwareCustomerInterface $customer, $email, $operation, $modificationDate)
    {
        $item = new DefaultNewsletterQueueItem($customer->getId(), null, $email, $operation, $modificationDate);

        $this->immidateAsyncQueueItems[$customer->getId() . '_' . $operation] = $item;
    }

    /**
     * @inheritdoc
     */
    public function executeImmidiateAsyncQueueItems()
    {
        if (!sizeof($this->immidateAsyncQueueItems)) {
            return;
        }

        $this->getLogger()->info('execute immidiate async queue items');

        if (count($this->immidateAsyncQueueItems) <= 1) { //avoid too many parallel scripts running
            foreach ($this->immidateAsyncQueueItems as $item) {
                $php = Console::getExecutable('php');
                $cmd = sprintf($php . ' ' . PIMCORE_PROJECT_ROOT . "/bin/console cmf:newsletter-sync --process-queue-item='%s'", $item->toJson());
                $this->getLogger()->info('execute async process queue item cmd: ' . $cmd);
                Console::execInBackground($cmd);
            }
        }
    }

    /**
     * @param NewsletterProviderHandlerInterface[] $newsletterProviderHandlers
     * @param bool $forceAllCustomers
     * @param bool $forceUpdate
     *
     * @return void
     */
    public function processQueue(array $newsletterProviderHandlers, $forceAllCustomers = false, $forceUpdate = false)
    {
        if (!$forceAllCustomers) {
            $this->processItemsFromQueue($newsletterProviderHandlers, $forceUpdate);
        } else {
            $this->processAllItems($newsletterProviderHandlers, $forceUpdate);
        }
    }

    /**
     * @param array $newsletterProviderHandler
     * @param NewsletterQueueItemInterface $newsletterQueueItem
     *
     * @return void
     */
    public function syncSingleQueueItem(array $newsletterProviderHandler, NewsletterQueueItemInterface $newsletterQueueItem)
    {
        $this->processQueueItems($newsletterProviderHandler, [$newsletterQueueItem], false);
    }

    /**
     * @param NewsletterQueueItemInterface $item
     *
     * @return void
     */
    public function removeFromQueue(NewsletterQueueItemInterface $item)
    {
        $db = Db::get();

        if (!is_null($item->getEmail())) {
            $db->executeQuery('delete from ' . self::QUEUE_TABLE . ' where customerId = ? and email = ? and operation = ? and modificationDate = ?', [
                $item->getCustomerId(), $item->getEmail(), $item->getOperation(), $item->getModificationDate()
            ]);
        } else {
            $db->executeQuery('delete from ' . self::QUEUE_TABLE . ' where customerId = ? and email is null and operation = ? and modificationDate = ?', [
                $item->getCustomerId(), $item->getOperation(), $item->getModificationDate()
            ]);
        }

        $this->getLogger()->info(sprintf(
            'newsletter queue item removed [customerId: %s, email: %s, operation: %s, modificationDate: %s]',
            $item->getCustomerId(),
            $item->getEmail(),
            $item->getOperation(),
            $item->getModificationDate()
        ));
    }

    /**
     * @return void
     */
    public function enqueueAllCustomers()
    {
        $this->getLogger()->info('add all customers to newsletter queue');
        /**
         * @var CustomerProviderInterface $customerProvider
         */
        $customerProvider = \Pimcore::getContainer()->get('cmf.customer_provider');

        $customerClassId = $customerProvider->getCustomerClassId();

        $idField = Service::getVersionDependentDatabaseColumnName('id');
        $publishedField = Service::getVersionDependentDatabaseColumnName('published');
        $sql = sprintf(
            "insert into %s (SELECT %s AS `customerId`,`email`, 'update' AS `operation`, ROUND(UNIX_TIMESTAMP(CURTIME(4)) * 1000) AS `modificationDate` FROM `object_%s` WHERE %s = 1 and id not in (select customerId from %s))",
            self::QUEUE_TABLE,
            $idField,
            $customerClassId,
            $publishedField,
            self::QUEUE_TABLE
        );

        Db::get()->executeQuery($sql);
    }

    /**
     * @return int
     */
    public function getQueueSize()
    {
        $sql = sprintf(
            'select count(*) from %s',
            self::QUEUE_TABLE
        );

        return Db::get()->fetchOne($sql);
    }

    /**
     * @param NewsletterProviderHandlerInterface[] $newsletterProviderHandlers
     * @param bool $forceUpdate
     */
    protected function processAllItems(array $newsletterProviderHandlers, $forceUpdate)
    {
        $customerProvider = \Pimcore::getContainer()->get('cmf.customer_provider');

        $list = $customerProvider->getList();

        $paginator = $this->paginator->paginate($list, 1, $this->maxItemsPerRound);
        $pageCount = $paginator->getPaginationData()['pageCount'];

        for ($i = 1; $i <= $pageCount; $i++) {
            $paginator = $this->paginator->paginate($list, $i, $this->maxItemsPerRound);
            $items = [];
            foreach ($paginator as $customer) {
                if ($item = $this->createUpdateItem($customer)) {
                    $items[] = $item;
                }
            }

            try {
                $this->processQueueItems($newsletterProviderHandlers, $items, $forceUpdate);
            } catch (\Exception $e) {
                $this->getLogger()->error('newsletter queue processing exception: ' . $e->getMessage());
            }

            \Pimcore::collectGarbage();
        }
    }

    /**
     * @param NewsletterProviderHandlerInterface[] $newsletterProviderHandlers
     */
    protected function processItemsFromQueue(array $newsletterProviderHandlers, $forceUpdate)
    {
        $db = Db::get();

        $select = $db->createQueryBuilder();
        $select
            ->select('*')
            ->from(self::QUEUE_TABLE);

        $rows = $db->fetchAllAssociative((string)$select);

        $paginator = $this->paginator->paginate($rows, 1, $this->maxItemsPerRound);
        $pageCount = $paginator->getPaginationData()['pageCount'];

        for ($i = 1; $i <= $pageCount; $i++) {
            $paginator = $this->paginator->paginate($rows, $i, $this->maxItemsPerRound);
            $items = [];
            foreach ($paginator as $row) {
                if ($item = $this->createItemFromData($row)) {
                    $items[] = $item;
                }
            }

            $this->processQueueItems($newsletterProviderHandlers, $items, $forceUpdate);

            \Pimcore::collectGarbage();
        }
    }

    /**
     * @param NewsletterProviderHandlerInterface[] $newsletterProviderHandlers
     * @param NewsletterQueueItemInterface[] $items
     */
    protected function processQueueItems(array $newsletterProviderHandlers, array $items, $forceUpdate)
    {
        /**
         * @var NewsletterQueueItemInterface[] $successfullItems
         */
        $successfullItems = [];
        $firstCall = true;
        foreach ($newsletterProviderHandlers as $newsletterProviderHandler) {
            $this->resetItemsStates($items);
            $newsletterProviderHandler->processCustomerQueueItems($items, $forceUpdate);
            $this->checkSuccessfullItems($successfullItems, $items, $firstCall);
            $firstCall = false;
        }

        // items need to be successful in all provider handlers otherwise they will stay in the queue.
        foreach ($successfullItems as $item) {
            $this->removeFromQueue($item);
        }
    }

    /**
     * @param NewsletterQueueItemInterface[] $successfullItems
     * @param NewsletterQueueItemInterface[] $processedItems
     * @param bool $firstCall
     */
    protected function checkSuccessfullItems(array &$successfullItems, array $processedItems, $firstCall = false)
    {
        if ($firstCall) {
            foreach ($processedItems as $item) {
                if ($item->wasSuccessfullyProcessed()) {
                    $successfullItems[] = $item;
                }
            }

            return;
        }

        $result = [];

        foreach ($processedItems as $item) {
            if (!$item->wasSuccessfullyProcessed()) {
                continue;
            }

            foreach ($successfullItems as $successfullItem) {
                if ($successfullItem == $item) {
                    $result[] = $item;
                    break;
                }
            }
        }

        $successfullItems = $result;
    }

    /**
     * @param NewsletterQueueItemInterface[] $items
     */
    protected function resetItemsStates(array $items)
    {
        foreach ($items as $item) {
            $item->setSuccessfullyProcessed(false);
            $item->setOverruledOperation(null);
        }
    }

    protected function createUpdateItem(CustomerInterface $customer)
    {
        return new DefaultNewsletterQueueItem($customer->getId(), $customer, $customer->getEmail(), self::OPERATION_UPDATE, 0);
    }

    /**
     * @param array $data
     *
     * @return NewsletterQueueItemInterface|false
     */
    protected function createItemFromData(array $data)
    {
        $customerProvider = \Pimcore::getContainer()->get('cmf.customer_provider');

        $customer = $customerProvider->getById($data['customerId']);

        if ($data['operation'] == self::OPERATION_UPDATE && !$customer) {
            return false;
        }

        return new DefaultNewsletterQueueItem($data['customerId'], $customer, $data['email'], $data['operation'], $data['modificationDate']);
    }
}

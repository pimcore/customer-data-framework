<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Queue;

use CustomerManagementFrameworkBundle\ActionTrigger\Action\ActionDefinitionInterface;
use CustomerManagementFrameworkBundle\Model\ActionTrigger\ActionDefinition;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Db;
use Pimcore\Model\Object\Customer;

class DefaultQueue implements QueueInterface
{
    use LoggerAware;

    const QUEUE_TABLE = 'plugin_cmf_actiontrigger_queue';

    public function addToQueue(ActionDefinitionInterface $action, CustomerInterface $customer)
    {
        $db = Db::get();

        $time = time();

        $this->logger->debug(
            sprintf('add action id %s for customer %s to queue', $action->getId(), $customer->getId())
        );

        $actionDateTimestamp = time() + $action->getActionDelay();

        $db->insert(
            self::QUEUE_TABLE,
            [
                'customerId' => $customer->getId(),
                'actionDate' => $actionDateTimestamp,
                'actionId' => $action->getId(),
                'creationDate' => $time,
                'modificationDate' => $time,
            ]
        );
    }

    public function processQueue()
    {
        $db = Db::get();

        $select = $db->select();
        $select
            ->from(
                self::QUEUE_TABLE
            )
            ->order('id asc')
            ->where('actionDate <= ?', time());

        $items = $db->fetchAll($select);

        foreach ($items as $item) {
            $this->processQueueItem($item);
        }
    }

    private function processQueueItem(array $item)
    {
        $logger = $this->logger;
        $logger->notice(sprintf('proccess entry ID %s', $item['id']));

        $action = ActionDefinition::getById($item['actionId']);
        $customer = Customer::getById($item['customerId']);

        if ($action && $customer) {
            \Pimcore::getContainer()->get('cmf.action_trigger.action_manager')->processAction($action, $customer);
        }

        $db = Db::get();
        $db->deleteWhere(self::QUEUE_TABLE, 'id='.intval($item['id']));
    }
}

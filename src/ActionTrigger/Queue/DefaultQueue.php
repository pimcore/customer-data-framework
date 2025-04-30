<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Queue;

use CustomerManagementFrameworkBundle\ActionTrigger\Action\ActionDefinitionInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironmentInterface;
use CustomerManagementFrameworkBundle\Model\ActionTrigger\ActionDefinition;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Db;
use Pimcore\Model\DataObject\AbstractObject;

class DefaultQueue implements QueueInterface
{
    use LoggerAware;

    const QUEUE_TABLE = 'plugin_cmf_actiontrigger_queue';

    public function addToQueue(
        ActionDefinitionInterface $action,
        CustomerInterface $customer,
        RuleEnvironmentInterface $environment
    ) {
        $db = Db::get();

        $time = time();

        $this->getLogger()->debug(
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
                'environment' => serialize($environment),
            ]
        );
    }

    public function processQueue()
    {
        $db = Db::get();

        $select = $db->createQueryBuilder();
        $select
            ->select('*')
            ->from(self::QUEUE_TABLE)
            ->addOrderBy('id', 'asc')
            ->andWhere('actionDate <= ' . time());

        $items = $db->fetchAllAssociative((string)$select);

        foreach ($items as $item) {
            $this->processQueueItem($item);
        }
    }

    private function processQueueItem(array $item)
    {
        $logger = $this->getLogger();
        $logger->notice(sprintf('proccess entry ID %s', $item['id']));

        $action = ActionDefinition::getById($item['actionId']);
        $customer = AbstractObject::getById($item['customerId']);

        /** @var RuleEnvironmentInterface $environment */
        $environment = unserialize($item['environment']);

        if ($action && $customer instanceof CustomerInterface) {
            \Pimcore::getContainer()->get('cmf.action_trigger.action_manager')->processAction(
                $action,
                $customer,
                $environment
            );
        }

        $db = Db::get();
        $db->executeQuery('DELETE FROM ' . self::QUEUE_TABLE . ' WHERE id = ?', [(int)$item['id']]);
    }
}

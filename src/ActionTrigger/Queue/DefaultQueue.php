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
    )
    {
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
                'environment' => serialize($environment)
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
        $db->deleteWhere(self::QUEUE_TABLE, 'id='.intval($item['id']));
    }
}

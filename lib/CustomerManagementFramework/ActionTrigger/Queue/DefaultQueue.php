<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:48
 */

namespace CustomerManagementFramework\ActionTrigger\Queue;

use CustomerManagementFramework\ActionTrigger\Rule;
use CustomerManagementFramework\Model\CustomerInterface;
use Pimcore\Db;

class DefaultQueue implements QueueInterface
{

    const QUEUE_TABLE = 'plugin_cmf_actiontrigger_queue';

    public function addToEventQueue(CustomerInterface $customer, Rule $rule, $actionDateTimestamp)
    {
        $db = Db::get();

        $time = time();

        $db->insert(self::QUEUE_TABLE, [
            'customerId' => $customer->getId(),
            'actionDate' => $actionDateTimestamp,
            'ruleId' => $rule->getId(),
            'creationDate' => $time,
            'modificationDate' => $time
        ]);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:48
 */

namespace CustomerManagementFramework\ActionTrigger\Queue;

use CustomerManagementFramework\ActionTrigger\ActionDefinition;
use CustomerManagementFramework\ActionTrigger\Event\EventInterface;
use CustomerManagementFramework\ActionTrigger\Rule;
use CustomerManagementFramework\ActionTrigger\Trigger\ActionDefinitionInterface;
use CustomerManagementFramework\Factory;
use Pimcore\Db;
use Psr\Log\LoggerInterface;

class DefaultQueue implements QueueInterface
{

    const QUEUE_TABLE = 'plugin_cmf_actiontrigger_queue';

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function addToQueue(ActionDefinitionInterface $action, EventInterface $event)
    {
        $db = Db::get();

        $time = time();

        $this->logger->debug(sprintf("add action id %s to queue", $action->getId()));

        $actionDateTimestamp = time() + $action->getActionDelay();

        $db->insert(self::QUEUE_TABLE, [
            'customerId' => $event->getCustomer()->getId(),
            'actionDate' => $actionDateTimestamp,
            'actionId' => $action->getId(),
            'creationDate' => $time,
            'modificationDate' => $time
        ]);
    }

    public function processQueue()
    {
        $db = Db::get();

        $select = $db->select();
        $select
            ->from(self::QUEUE_TABLE
            )
            ->order("id asc")
            ->where('actionDate <= ?', time())
        ;


        $paginator = new \Zend_Paginator(new \Zend_Paginator_Adapter_DbSelect($select));
        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber(1);

        $totalPages = $paginator->getPages()->pageCount;
        for($i=1; $i<=$totalPages; $i++) {
            $paginator->setCurrentPageNumber($i);

            foreach($paginator as $item) {
                $this->processQueueItem($item);
            }
        }
    }

    private function processQueueItem(array $item)
    {
        $logger = $this->logger;
        $logger->notice(sprintf("proccess entry ID %s", $item['id']));

        $action = ActionDefinition::getById($item['actionId']);

        Factory::getInstance()->getActionTriggerActionManager()->processAction($action);
        $db = Db::get();
        $db->delete(self::QUEUE_TABLE, 'id='.intval($item['id']));
    }
}
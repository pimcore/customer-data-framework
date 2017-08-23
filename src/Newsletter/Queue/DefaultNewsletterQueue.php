<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Newsletter\Queue;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Db;

class DefaultNewsletterQueue implements NewsletterQueueInterface
{
    const QUEUE_TABLE = 'plugin_cmf_newsletter_queue';

    public function enqueueCustomer(CustomerInterface $customer, $operation, $email = null)
    {
        $db = Db::get();
        $db->query(
            'insert into ' . self::QUEUE_TABLE . ' (customerId, email, operation, modificationDate) values (:customerId,:email,:operation,:modificationDate) on duplicate key update operation = :operation, modificationDate = :modificationDate, email = :email',
            [
                'customerId'=>$customer->getId(),
                'email'=>!is_null($email) ? $email : $customer->getEmail(),
                'operation'=>$operation,
                'modificationDate' => round(microtime(true) * 1000)
            ]
        );
    }
}

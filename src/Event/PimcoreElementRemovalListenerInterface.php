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

namespace CustomerManagementFrameworkBundle\Event;

use Pimcore\Event\Model\ElementEventInterface;

interface PimcoreElementRemovalListenerInterface
{
    /**
     * performs cleaning up when Pimcore elements are deleted,
     * namely removes segment assignments from assignment, queue and index tables
     *
     *
     * @return void
     */
    public function onPostDelete(ElementEventInterface $event);
}

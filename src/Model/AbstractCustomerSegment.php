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

namespace CustomerManagementFrameworkBundle\Model;

use CustomerManagementFrameworkBundle\Service\ObjectToArray;

abstract class AbstractCustomerSegment extends \Pimcore\Model\DataObject\Concrete implements CustomerSegmentInterface
{
    public function getDataForWebserviceExport()
    {
        $data = ObjectToArray::getInstance()->toArray($this);

        if ($data['group']) {
            $data['group'] = $data['group']['id'];
        }

        return $data;
    }
}

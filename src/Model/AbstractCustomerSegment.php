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

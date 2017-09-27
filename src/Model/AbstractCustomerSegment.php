<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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

<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ExportToolkit\ConditionModificator;

use Elements\Bundle\ExportToolkitBundle\ExportService\IConditionModificator;
use Elements\Bundle\ExportToolkitBundle\ExportService\IListModificator;
use Pimcore\Db\ZendCompatibility\QueryBuilder;
use Pimcore\Model\Object\CustomerSegmentGroup;
use Pimcore\Model\Object\Listing;

class CustomerSegments implements IConditionModificator, IListModificator
{
    public static function modify($configName, $condidition)
    {
        // noop - all done in modifyList
    }

    public static function modifyList($configName, Listing $list)
    {
        $list->onCreateQuery(
            function (QueryBuilder $query) {
                $query->where(
                    '(group__id in (select o_id from object_' . CustomerSegmentGroup::classId() . ' where o_published=1 and exportNewsletterProvider = 1))'
                );
            }
        );
    }
}

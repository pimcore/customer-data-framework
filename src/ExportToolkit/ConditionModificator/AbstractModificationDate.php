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

use ExportToolkit\ExportService\IConditionModificator;
use ExportToolkit\ExportService\IListModificator;
use Pimcore\Db;
use Pimcore\Model\Object\Listing;

abstract class AbstractModificationDate implements IConditionModificator, IListModificator
{
    public static function modify($configName, $condition)
    {
        // noop - all done in modifyList
    }

    /**
     * Modify list, e.g. add joins which can be used in condition
     *
     * @param $configName
     * @param Listing|Listing\Dao $list
     *
     * @return $this
     */
    public static function modifyList($configName, Listing $list)
    {
        $list->onCreateQuery(
            function (Db\ZendCompatibility\QueryBuilder $query) {
                $subQuery = static::buildNoteSubQuery();

                $query->joinLeft(
                    ['notes' => new Db\ZendCompatibility\Expression('('.$subQuery->__toString().')')],
                    'notes.cid = o_id',
                    []
                );

                $query->where(
                    '(notes.date IS NULL) OR (o_modificationDate IS NULL) OR (o_modificationDate > notes.date)'
                );
            }
        );
    }

    /**
     * @return Db\ZendCompatibility\QueryBuilder
     */
    protected static function buildNoteSubQuery()
    {
        $query = Db::get()->select();
        $query->from(
            ['n' => 'notes'],
            [
                'cid',
                'date' => new Db\ZendCompatibility\Expression('MAX(n.date)'),
            ]
        );

        $query->group('n.cid');

        return $query;
    }
}

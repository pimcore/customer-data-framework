<?php

namespace CustomerManagementFramework\ExportToolkit\ConditionModificator;

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
     * @return $this
     */
    public static function modifyList($configName, Listing $list)
    {
        $list->onCreateQuery(function(\Zend_Db_Select $query) {
            $subQuery = static::buildNoteSubQuery();

            $query->joinLeft(
                ['notes' => new \Zend_Db_Expr('(' . $subQuery->__toString() . ')')],
                'notes.cid = o_id',
                []
            );

            $query->where('(notes.date IS NULL) OR (o_modificationDate IS NULL) OR (o_modificationDate > notes.date)');
        });
    }

    /**
     * @return \Zend_Db_Select
     */
    protected static function buildNoteSubQuery()
    {
        $query = Db::get()->select();
        $query->from(
            ['n' => 'notes'],
            [
                'cid',
                'date' => new \Zend_Db_Expr('MAX(n.date)')
            ]
        );

        $query->group('n.cid');

        return $query;
    }
}

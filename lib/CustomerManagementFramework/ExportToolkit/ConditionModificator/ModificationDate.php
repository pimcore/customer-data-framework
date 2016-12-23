<?php

namespace CustomerManagementFramework\ExportToolkit\ConditionModificator;

use ExportToolkit\ExportService\IConditionModificator;
use ExportToolkit\ExportService\IListModificator;
use Pimcore\Model\Object\Listing;

class ModificationDate implements IConditionModificator, IListModificator
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
            $query->joinLeft(
                ['notes' => new \Zend_Db_Expr('(SELECT n.cid, MAX(n.date) AS date FROM notes n GROUP BY n.cid)')],
                'notes.cid = o_id',
                []
            );

            $query->where('(notes.date IS NULL) OR (o_modificationDate IS NULL) OR (o_modificationDate > notes.date)');
        });
    }
}

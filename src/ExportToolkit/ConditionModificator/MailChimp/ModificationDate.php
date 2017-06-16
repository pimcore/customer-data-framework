<?php

namespace CustomerManagementFrameworkBundle\ExportToolkit\ConditionModificator\MailChimp;

use CustomerManagementFrameworkBundle\ExportToolkit\ConditionModificator\AbstractModificationDate;
use CustomerManagementFrameworkBundle\ExportToolkit\ExportService\MailChimpExportService;

class ModificationDate extends AbstractModificationDate
{
    /**
     * @return \Zend_Db_Select
     */
    protected static function buildNoteSubQuery()
    {
        $exportService = \Pimcore::getDiContainer()->get(MailChimpExportService::class);

        $query = parent::buildNoteSubQuery();
        $query->where('type = ?', $exportService->getExportNoteType());
        $query->where('description = ?', $exportService->getListId());

        return $query;
    }
}

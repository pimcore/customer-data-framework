<?php

namespace CustomerManagementFrameworkBundle\ExportToolkit\ConditionModificator\MailChimp;

use CustomerManagementFrameworkBundle\ExportToolkit\ConditionModificator\AbstractModificationDate;
use CustomerManagementFrameworkBundle\ExportToolkit\ExportService\MailChimpExportService;
use Pimcore\Db\ZendCompatibility\QueryBuilder;

class ModificationDate extends AbstractModificationDate
{
    /**
     * @return QueryBuilder
     */
    protected static function buildNoteSubQuery()
    {
        $exportService = \Pimcore::getContainer()->get('cmf.mailchimp.export_service');

        $query = parent::buildNoteSubQuery();
        $query->where('type = ?', $exportService->getExportNoteType());
        $query->where('description = ?', $exportService->getListId());

        return $query;
    }
}

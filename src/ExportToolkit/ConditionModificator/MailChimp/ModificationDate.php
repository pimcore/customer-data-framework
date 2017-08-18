<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ExportToolkit\ConditionModificator\MailChimp;

use CustomerManagementFrameworkBundle\ExportToolkit\ConditionModificator\AbstractModificationDate;
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

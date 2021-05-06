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

namespace CustomerManagementFrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Pimcore\Db;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Migration to add segment assignment
 */
class Version20171113110855 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $sqlPath = __DIR__ . '/../Resources/sql/segmentAssignment/';
        $sqlFileNames = ['datamodel.sql', 'storedFunctionDocument.sql', 'storedFunctionAsset.sql', 'storedFunctionObject.sql'];
        $db = Db::get();

        foreach ($sqlFileNames as $fileName) {
            $statement = file_get_contents($sqlPath.$fileName);
            $db->query($statement);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // downgrading would result in data loss and is not deemed necessary at the moment
    }
}

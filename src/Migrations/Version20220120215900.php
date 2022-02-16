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
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

class Version20220120215900 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $deletionsTable = $schema->getTable('plugin_cmf_deletions');

        if (!$deletionsTable->hasPrimaryKey()) {
            $this->addSql('SET foreign_key_checks = 0');
            $this->addSql('ALTER TABLE `plugin_cmf_deletions` ADD PRIMARY KEY (`id`, `entityType`, `type`);');
            $this->addSql('SET foreign_key_checks = 1');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        $this->addSql('SET foreign_key_checks = 0');
        $this->addSql('ALTER TABLE `plugin_cmf_deletions` DROP INDEX `PRIMARY`;');
        $this->addSql('SET foreign_key_checks = 1');
    }
}

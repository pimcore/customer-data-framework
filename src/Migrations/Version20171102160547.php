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
use Pimcore\Bundle\NumberSequenceGeneratorBundle\Installer;
use Pimcore\Db;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171102160547 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $db = Db::get();

        if ($rows = $db->fetchAll('select * from plugin_cmf_sequence_numbers')) {

            /**
             * @var Installer $installer
             */
            $installer = \Pimcore::getContainer()->get(Installer::class);

            if (!$installer->isInstalled()) {
                $installer->migrateInstall($schema, $this->version);
            }

            if (!$installer->isInstalled()) {
                throw new \Exception('number sequence generator needs to be installed first.');
            }

            foreach ($rows as $row) {
                $db->insert('bundle_number_sequence_generator_register', [
                    'register' => $row['name'],
                    'counter' => $row['number']
                ]);
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}

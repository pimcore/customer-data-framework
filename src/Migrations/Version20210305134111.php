<?php

namespace CustomerManagementFrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Pimcore\AssetMetadataClassDefinitionsBundle\Model\Configuration\Dao;
use Pimcore\Config;
use Pimcore\Db;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use Pimcore\Model\Tool\SettingsStore;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20210305134111 extends AbstractPimcoreMigration
{
    public function doesSqlMigrations(): bool
    {
        return false;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $installed = Db::get()->fetchOne('SELECT `key` FROM users_permission_definitions WHERE `key` = :key', [
            'key' => 'plugin_cmf_perm_activityview'
        ]);

        if($installed) {
            SettingsStore::set('BUNDLE_INSTALLED__CustomerManagementFrameworkBundle\\PimcoreCustomerManagementFrameworkBundle', true, 'bool', 'pimcore');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}

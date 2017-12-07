<?php

namespace CustomerManagementFrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Pimcore\Db;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Migration to add filter definition
 */
class Version20171207150300 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $sqlPath = __DIR__ . '/../Resources/sql/filterDefinition/';
        $sqlFileNames = ['datamodel.sql'];
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

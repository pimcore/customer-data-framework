<?php

declare(strict_types=1);

namespace CustomerManagementFrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20220316141955 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('SET foreign_key_checks = 0');
        $this->addSql('ALTER TABLE `plugin_cmf_deletions` DROP INDEX IF EXISTS `PRIMARY`;');
        $this->addSql('SET foreign_key_checks = 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

<?php

namespace CustomerManagementFrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use Pimcore\Model\DataObject\ClassDefinition;

/**
 * Adding two additional fields 'useAsTargetGroup' and 'targetGroup' to CustomerSegment class
 */
class Version20171218194028 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->writeMessage("Adding two additional fields 'useAsTargetGroup' and 'targetGroup' to CustomerSegment class");

        if ($this->isDryRun()) {
            // nothing to do
            return;
        }

        $segmentDefinition = ClassDefinition::getByName("CustomerSegment");

        $checkbox = new ClassDefinition\Data\Checkbox();
        $checkbox->setName("useAsTargetGroup");
        $checkbox->setTitle("Use As Target Group");
        $checkbox->setVisibleGridView(false);
        $checkbox->setVisibleSearch(false);

        $targetGroup = new ClassDefinition\Data\TargetGroup();
        $targetGroup->setName("targetGroup");
        $targetGroup->setTitle("Linked TargetGroup");
        $targetGroup->setNoteditable(true);
        $targetGroup->setVisibleGridView(false);
        $targetGroup->setVisibleSearch(false);

        $segmentDefinition->addNewDataField("calculated", $targetGroup);
        $segmentDefinition->addNewDataField("calculated", $checkbox);

        $segmentDefinition->save();

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->writeMessage("Removing two fields 'useAsTargetGroup' and 'targetGroup' to CustomerSegment class");

        if ($this->isDryRun()) {
            // nothing to do
            return;
        }

        $segmentDefinition = ClassDefinition::getByName("CustomerSegment");

        $segmentDefinition->removeExistingDataField("useAsTargetGroup");
        $segmentDefinition->removeExistingDataField("targetGroup");

        $segmentDefinition->save();

    }
}

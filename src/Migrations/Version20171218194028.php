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

        $segmentDefinition = ClassDefinition::getByName('CustomerSegment');

        $checkbox = new ClassDefinition\Data\Checkbox();
        $checkbox->setName('useAsTargetGroup');
        $checkbox->setTitle('Use As Target Group');
        $checkbox->setVisibleGridView(false);
        $checkbox->setVisibleSearch(false);

        $targetGroup = new ClassDefinition\Data\TargetGroup();
        $targetGroup->setName('targetGroup');
        $targetGroup->setTitle('Linked TargetGroup');
        $targetGroup->setNoteditable(true);
        $targetGroup->setVisibleGridView(false);
        $targetGroup->setVisibleSearch(false);

        $this->addNewDataField($segmentDefinition, 'calculated', $targetGroup);
        $this->addNewDataField($segmentDefinition, 'calculated', $checkbox);

        $segmentDefinition->save();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // downgrading would result in data loss and is not deemed necessary at the moment
    }

    /**
     * Adds given data field after existing field with given field name. If existing field is not found, nothing is added.
     *
     * @param ClassDefinition $class
     * @param string $fieldNameToAddAfter
     * @param ClassDefinition\Data $fieldToAdd
     * @param ClassDefinition\Layout|null $layoutComponent
     */
    private function addNewDataField(
        ClassDefinition $class,
        string $fieldNameToAddAfter,
        ClassDefinition\Data $fieldToAdd,
        ClassDefinition\Layout $layoutComponent = null
    ) {
        $found = false;
        $index = null;
        if (null === $layoutComponent) {
            $layoutComponent = $class->getLayoutDefinitions();
        }
        $children = $layoutComponent->getChildren();
        //try to find field
        foreach ($children as $index => $child) {
            if ($child->getName() == $fieldNameToAddAfter) {
                $found = true;
                break;
            }
        }
        if ($found) {
            //if found, insert toAdd after index
            array_splice($children, $index + 1, 0, [$fieldToAdd]);
            $layoutComponent->setChildren($children);
        } else {
            //if not found, call recursive
            foreach ($children as $index => $child) {
                if ($child instanceof ClassDefinition\Layout && $child->getChildren()) {
                    $this->addNewDataField($class, $fieldNameToAddAfter, $fieldToAdd, $child);
                }
            }
        }
    }
}

<?php

namespace CustomerManagementFrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Pimcore\Db;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\User\Permission\Definition;
use Pimcore\Model\User\Permission\Definition\Dao;

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
        // run sql statements
        $sqlPath = __DIR__ . '/../Resources/sql/filterDefinition/';
        $sqlFileNames = ['datamodel.sql'];
        $db = Db::get();

        foreach ($sqlFileNames as $fileName) {
            $statement = file_get_contents($sqlPath.$fileName);
            $db->query($statement);
        }

        // Update CustomerSegmentGroup -> add filterSortOrder field
        $customerSegmentGroupClass = ClassDefinition::getByName('CustomerSegmentGroup');
        $filterSortOrder = new ClassDefinition\Data\Numeric();
        $filterSortOrder->setName('filterSortOrder');
        $filterSortOrder->setTitle('Filter sort order');
        $filterSortOrder->setVisibleGridView(true);
        $filterSortOrder->setInteger(true);
        $filterSortOrder->setTooltip('Set the sort order for field in customer search. The higher the sort order the higher the priority.');
        $filterSortOrder->setVisibleSearch(false);
        $this->addNewDataField($customerSegmentGroupClass, 'showAsFilter', $filterSortOrder);
        $customerSegmentGroupClass->save();

        // workaround for a strange pimcore bug:
        $json = ClassDefinition\Service::generateClassDefinitionJson($customerSegmentGroupClass);
        ClassDefinition\Service::importClassDefinitionFromJson($customerSegmentGroupClass, $json, true);

        // add customer view admin permission
        $permission = new Definition();
        $permission->setKey('plugin_cmf_perm_customerview_admin');
        $resource = new Dao();
        $resource->configure();
        $resource->setModel($permission);
        $resource->save();
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
     * @param $fieldNameToAddAfter
     * @param ClassDefinition\Data $fieldToAdd
     * @param ClassDefinition\Layout|null $layoutComponent
     */
    private function addNewDataField(
        ClassDefinition $class,
        $fieldNameToAddAfter,
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

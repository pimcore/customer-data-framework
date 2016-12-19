<?php

namespace CustomerManagementFramework\Mailchimp\AttributeClusterInterpreter;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use ExportToolkit\ExportService\AttributeClusterInterpreter\AbstractAttributeClusterInterpreter;
use Pimcore\Model\Object\AbstractObject;

class Api extends AbstractAttributeClusterInterpreter
{
    /**
     * This method is executed before the export is launched.
     * For example it can be used to clean up old export files, start a database transaction, etc.
     * If not needed, just leave the method empty.
     */
    public function setUpExport()
    {
        // noop
    }

    /**
     * This method is executed after all defined attributes of an object are exported.
     * The to-export data is stored in the array $this->data[OBJECT_ID].
     * For example it can be used to write each exported row to a destination database,
     * write the exported entries to a file, etc.
     * If not needed, just leave the method empty.
     *
     * @param AbstractObject|CustomerInterface $object
     */
    public function commitDataRow(AbstractObject $object)
    {
        $mailchimpExporter = Factory::getInstance()->getMailchimpExportService();

        dump([
            'wasCreated'  => $mailchimpExporter->wasCreated($object),
            'needsUpdate' => $mailchimpExporter->needsUpdate($object),
            'lastExport'  => $mailchimpExporter->getLastExportDateTime($object)
        ]);

        $note = Factory::getInstance()->getMailchimpExportService()->createExportNote($object);
        $note->save();

        dump($this->data[$object->getId()]);
    }

    /**
     * This method is executed after all objects are exported.
     * If not cleaned up in the commitDataRow-method, all exported data is stored in the array $this->data.
     * For example it can be used to write all data to a xml file or commit a database transaction, etc.
     *
     */
    public function commitData()
    {
        // dump($this->data);
    }

    /**
     * This method is executed of an object is not exported (anymore).
     * For example it can be used to remove the entries from a destination database, etc.
     *
     * @param AbstractObject $object
     */
    public function deleteFromExport(AbstractObject $object)
    {
        // noop
    }
}

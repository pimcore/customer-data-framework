<?php

namespace CustomerManagementFramework\RESTApi;

use Pimcore\Model\Element\ElementInterface;

interface CrudInterface
{
    /**
     * @param \Zend_Controller_Request_Http $request
     * @return $this
     */
    public function setRequest(\Zend_Controller_Request_Http $request);

    /**
     * @param mixed $id
     * @return ElementInterface
     */
    public function loadRecord($id);

    /**
     * @return Response
     */
    public function listRecords();

    /**
     * @param array $data
     * @return Response
     */
    public function createRecord(array $data);

    /**
     * @param ElementInterface $record
     * @return Response
     */
    public function readRecord(ElementInterface $record);

    /**
     * @param ElementInterface $record
     * @param array $data
     * @return Response
     */
    public function updateRecord(ElementInterface $record, array $data);

    /**
     * @param ElementInterface $record
     * @return Response
     */
    public function deleteRecord(ElementInterface $record);
}

<?php
/*namespace CustomerManagementFrameworkBundle\Controller\Report;

class TermSegmentBuilderController extends \Pimcore\Controller\Action\Admin
{
    public function getSegmentBuilderDefinitionsAction()
    {
        \Pimcore\Model\Object\AbstractObject::setHideUnpublished(true);

        $list = new \Pimcore\Model\Object\TermSegmentBuilderDefinition\Listing;
        $list = $list->load();

        $result = ["data" => []];

        foreach($list as $entry ){
            $result["data"][] = [
                'id' =>  $entry->getId(),
                'name' => $entry->getName()
            ];
        }

        $this->_helper->json($result);
    }
}*/

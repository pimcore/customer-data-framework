<?php

namespace CustomerManagementFrameworkBundle\Controller\Report;

use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/term-segment-builder")
 */
class TermSegmentBuilderController extends AdminController
{
    /**
     * @Route("/get-segment-builder-definitions")
     */
    public function getSegmentBuilderDefinitionsAction()
    {
        \Pimcore\Model\Object\AbstractObject::setHideUnpublished(true);

        $list = new \Pimcore\Model\Object\TermSegmentBuilderDefinition\Listing;
        $list = $list->load();

        $result = ["data" => []];

        foreach ($list as $entry) {
            $result["data"][] = [
                'id' => $entry->getId(),
                'name' => $entry->getName(),
            ];
        }

        return $this->json($result);
    }
}

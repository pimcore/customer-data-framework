<?php
/**
 * Created by PhpStorm.
 * User: dschroffner
 * Date: 04.12.2017
 * Time: 15:03
 */


namespace CustomerManagementFrameworkBundle\Controller\Admin\Customers;

use CustomerManagementFrameworkBundle\Controller\Admin;
use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/customers/filter-definition")
 */
class FilterDefinitionController extends Admin {

    /**
     * @param Request $request
     * @Route("/test")
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function testAction(Request $request)
    {
        $item = new FilterDefinition;
        $item->setName('First Filter');
        $item->setDefinition([
            'name' => 'test',
            'email' => 'test@test.at',
            'segments' => [
                6692924 => [6697161]
            ],
        ]);
        $item->setAllowedUserIds([2,3,5,7,9,11,13,17]);
        $item->setShowSegments([6692924,6692950,6694248,6697154,6697869]);
        $item->setReadOnly(true);
        $item->setShortcutAvailable(true);
        $item->save();

        $item2 = new FilterDefinition;
        $item2->setName('Second Filter');
        $item2->setDefinition([
            'name' => 'test2',
            'email' => 'test2@test2.at',
        ]);
        $item2->setAllowedUserIds([11,13,17]);
        $item2->setShowSegments([6692924,6692950,6694248]);
        $item2->setReadOnly(false);
        $item2->setShortcutAvailable(false);
        $item2->save();

        return $this->json([
            'id' => $item->getId(),
            'id2' => $item2->getId(),
        ]);

        /*
        $listing = new FilterDefinition\Listing();

        if(!$this->getUser()->isAllowed('plugin_cmf_perm_customerview_admin')) {
            var_dump("filtering");
            die;
            // fetch roles of user
            $userIds = $this->getUser()->getRoles();
            // fetch id of user
            $userIds[] = $this->getUser()->getId();
            // build user ids condition for filter definition
            $listing->setUserIdsCondition($userIds);
        }

        foreach($listing->load() as $item) {
            var_dump($item->getName());
            var_dump($item->getShowSegments());
        }
        die();

        //return $this->json([]);
        */
    }

    /**
     * @param Request $request
     * @Route("/save")
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function saveAction(Request $request)
    {
        return $this->json(['ok'=>false]);
    }
}

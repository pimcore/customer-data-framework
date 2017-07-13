<?php

namespace CustomerManagementFrameworkBundle\Controller\Admin;

use CustomerManagementFrameworkBundle\Controller\Admin;
use CustomerManagementFrameworkBundle\Factory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Pimcore\Controller\Configuration\TemplatePhp;

/**
 * @Route("/duplicates")
 */
class DuplicatesController extends Admin
{


    public function init()
    {
        parent::init();

        \Pimcore\Model\Object\AbstractObject::setHideUnpublished(true);
    }

    /**
     * @param Request $request
     * @Route("/list")
     */
    public function listAction(Request $request)
    {

        $paginator = \Pimcore::getContainer()->get('cmf.customer_duplicates_index')->getPotentialDuplicates(
            $request->get('page', 1),
            100,
            $request->get('declined')
        );

        return $this->render(
            'PimcoreCustomerManagementFrameworkBundle:Admin\Duplicates:list.html.php',
            [
                'paginator' => $paginator,
                'duplicates' => $paginator->getCurrentItems(),
                'duplicatesView' => \Pimcore::getContainer()->get('cmf.customer_duplicates_view'),
            ]
        );
    }

    public function falsePositivesAction()
    {
        $this->enableLayout();

        $paginator = Factory::getInstance()->getDuplicatesIndex()->getFalsePositives($this->getParam('page', 1), 200);

        $this->view->paginator = $paginator;
    }

    /**
     * @param Request $request
     * @Route("/decline/{id}")
     */
    public function declineAction(Request $request)
    {
        try {
            \Pimcore::getContainer()->get('cmf.customer_duplicates_index')->declinePotentialDuplicate(
                $request->get('id')
            );

            return new JsonResponse(["success" => true]);
        } catch (\Exception $e) {
            return new JsonResponse(["success" => false, "msg" => $e->getMessage()]);
        }
    }
}

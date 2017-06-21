<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 13:19
 */

namespace CustomerManagementFrameworkBundle\Controller\Admin;

use Pimcore\Db\ZendCompatibility\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\Annotation\Route;
use Pimcore\Controller\Configuration\TemplatePhp;
use Zend\Paginator\Paginator;



/**
 * @Route("/activities")
 */
class ActivitiesController extends \Pimcore\Bundle\AdminBundle\Controller\AdminController
{
    public function onKernelController(FilterControllerEvent $event)
    {
        //$this->checkPermission('plugin_customermanagementframework_activityview');
    }

    /**
     * @param Request $request
     * @Route("/list")
     */
    public function listAction(Request $request)
    {

        $types = null;
        $type = null;
        $activities = null;
        $paginator = null;

        if ($customer = \Pimcore\Model\Object\Customer::getById($request->get('customerId'))) {

            $list = \Pimcore::getContainer()->get('cmf.activity_store')->getActivityList();
            $list->setOrderKey('activityDate');
            $list->setOrder('desc');

            $select = $list->getQuery(false);
            $select->where("customerId = ?", $customer->getId());


            $select = $list->getQuery();
            $select->reset(QueryBuilder::COLUMNS);
            $select->reset(QueryBuilder::FROM);
            $select->from(\CustomerManagementFrameworkBundle\ActivityStore\MariaDb::ACTIVITIES_TABLE,
                ["type" => "distinct(type)"]
            );
            $types = \Pimcore\Db::get()->fetchCol($select);

            if ($type = $request->get('type')) {
                $select = $list->getQuery(false);
                $select->where("type = ?", $type);
            }


            $paginator = new Paginator($list);
            $paginator->setItemCountPerPage(25);
            $paginator->setCurrentPageNumber($request->get('page', 1));

        }


        return $this->render('PimcoreCustomerManagementFrameworkBundle:Admin\Activities:list.html.php', [
            'types' => $types,
            'type' => $type,
            'activities' => $paginator,
            'customer' => $customer,
            'activityView' => \Pimcore::getContainer()->get('cmf.activity_view')
        ]);
    }


    /**
     * @param Request $request
     * @Route("/detail")
     */
    public function detailAction(Request $request)
    {

        $activity = \Pimcore::getContainer()->get("cmf.activity_store")->getEntryById($request->get('activityId'));

        return $this->render('PimcoreCustomerManagementFrameworkBundle:Admin\Activities:detail.html.php', [
            'activity' => $activity,
            'activityView' => \Pimcore::getContainer()->get('cmf.activity_view')
        ]);
    }
}

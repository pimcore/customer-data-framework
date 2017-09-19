<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 2017-09-12
 * Time: 2:03 PM
 */

namespace CustomerManagementFrameworkBundle\Controller\Admin;


use CustomerManagementFrameworkBundle\SegmentAssignment\Indexer\IndexerInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Model\Document;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SegmentAssignmentController
 *
 * @Route("/segment-assignment")
 *
 * @package CustomerManagementFrameworkBundle\Controller\Admin
 */
class SegmentAssignmentController extends AdminController {

    /**
     *
     * @Route("/index-test")
     * @param Request $request
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function indexTestAction(Request $request) {
        /* @var $test IndexerInterface */
        $test = $this->get(IndexerInterface::class);

        $test->processQueue();

        return $this->json(true);
    }

    /**
     * @Route("/segment-retrieval")
     * @param Request $request
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function segmentRetrievalTest(Request $request) {
        /* @var $segmentManager SegmentManagerInterface */
        $segmentManager = $this->get(SegmentManagerInterface::class);

        $segments = $segmentManager->getSegmentsForElement(Document::getById(21));
        var_dump($segments); die;
        return $this->json(var_export($segments, true));
    }

    /**
     * @param Request $request
     * @Route("/list")
     */
    public function listAction(Request $request) {

    }
}
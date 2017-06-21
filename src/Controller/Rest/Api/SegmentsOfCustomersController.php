<?php
namespace CustomerManagementFrameworkBundle\Controller\Rest\Api;

use CustomerManagementFrameworkBundle\Controller\Rest\RestHandlerController;
use CustomerManagementFrameworkBundle\RESTApi\Exception\ExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/segments-of-customers")
 */
class SegmentsOfCustomersController extends RestHandlerController
{

    /**
     * @param Request $request
     * @Route("")
     * @Method({"PUT", "POST"})
     */
    public function updateRecordsAction(Request $request)
    {
        $handler  = $this->getHandler();
        $response = null;

        try {
            $response = $handler->updateRecords($request);
        } catch (ExceptionInterface $e) {
            $response = $this->createErrorResponse(
                $e->getMessage(),
                $e->getResponseCode() > 0 ? $e->getResponseCode() : 400
            );
        }

        return $response;
    }

    /**
     * @return \CustomerManagementFrameworkBundle\RESTApi\SegmentsOfCustomerHandler
     */
    protected function getHandler()
    {
        return \Pimcore::getContainer()->get('cmf.rest.segments_of_customer_handler');
    }
}

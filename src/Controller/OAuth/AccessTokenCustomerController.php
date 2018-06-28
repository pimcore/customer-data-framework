<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Controller\OAuth;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\CustomerSaveValidator\Exception\DuplicateCustomerException;
use CustomerManagementFrameworkBundle\OAuth\Service\UserInfo;
use CustomerManagementFrameworkBundle\RESTApi\Exception\MissingRequestBodyException;
use CustomerManagementFrameworkBundle\RESTApi\Traits\CustomerGenerator;
use CustomerManagementFrameworkBundle\RESTApi\Traits\ResourceUrlGenerator;
use CustomerManagementFrameworkBundle\RESTApi\Traits\ResponseGenerator;
use Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse;
use Pimcore\Controller\FrontendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class AccessTokenCustomerController extends FrontendController
{
    use ResponseGenerator;
    use CustomerGenerator;
    use ResourceUrlGenerator;

    /**
     * @param Request $request
     * @param UserInfo $userInfo
     * @Route("/accesstoken", name="cmf_read_customer_access_token_path")
     * @Method({"GET"})
     * @return JsonResponse|Response
     */
    public function readCustomerByAccessTokenRequest(Request $request, UserInfo $userInfo)
    {
        try {
            $customer = $this->loadCustomerByAccessTokenRequest($request, $userInfo);
        }
        catch(\Exception $error) {
            return $this->sendJSONError($error);
        }

        return $this->createCustomerResponse($customer, $request);
    }

    /**
     * @param Request $request
     * @param CustomerProviderInterface $customerProvider
     * @param UserInfo $userInfo
     * @Route("/accesstoken", name="cmf_write_customer_access_token_path")
     * @Method({"POST", "PUT"})
     * @return \CustomerManagementFrameworkBundle\RESTApi\Response|null|\Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateCustomerByAccessTokenRequest(Request $request, CustomerProviderInterface $customerProvider, UserInfo $userInfo)
    {
        $customer = $this->loadCustomerByAccessTokenRequest($request,$userInfo);
        $data = $this->getRequestData($request);

        try {
            $customerProvider->update($customer, $data);
            $customer->save();
        }
        catch(DuplicateCustomerException $e) {
            $duplicateCustomer = $e->getDuplicateCustomer();
            $response = $this->createErrorResponse('duplicate customer found');
            $content = json_decode($response->getContent(), true);
            $content['duplicateCustomer'] = $duplicateCustomer->getId();
            $response->setContent(json_encode($content));

            return $response;
        } catch (\Exception $e) {
            return $this->sendJSONError($e);
        }

        return $this->createCustomerResponse($customer, $request);
    }

    /**
     * @param Request $request
     * @param UserInfo $userInfo
     * @return \CustomerManagementFrameworkBundle\Model\CustomerInterface|\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function loadCustomerByAccessTokenRequest(Request $request, UserInfo $userInfo)
    {
        $customer = $userInfo->getCustomerByAccessTokenRequest($request);

        if (!$customer) {
            throw new ResourceNotFoundException(sprintf('Customer with ACCESS-TOKEN %d was not found', $request->header("authorization") ?? $request->header("Authorization")));
        }

        return $customer;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    protected function getRequestData(Request $request)
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        if (null === $data) {
            throw new MissingRequestBodyException(
                'Request body is no valid JSON',
                Response::HTTP_BAD_REQUEST
            );
        }

        return $data;
    }

    /**
     * @param \Exception $error
     * @return JsonResponse
     */
    protected function sendJSONError(\Exception $error){
        return new JsonResponse(['success'=>false,'error'=>$error->getMessage()],$error->getCode());
    }
}

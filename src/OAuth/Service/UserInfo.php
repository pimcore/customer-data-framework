<?php
/**
 * Created by PhpStorm.
 * User: fbruenner
 * Date: 20.06.2018
 * Time: 15:06
 */

namespace CustomerManagementFrameworkBundle\OAuth\Service;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Entity\Service\Auth\AccessToken;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pimcore\Tool\RestClient\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserInfo{

    /**
     * @var EntityManagerInterface
     */
    private $entity_manager = null;

    /**
     * @var AuthorizationServer
     */
    private $authorizationServer;

    /**
     * @var CustomerProviderInterface
     */
    private $customerProvider;

    public function __construct(EntityManagerInterface $entity_manager, AuthorizationServer $authorizationServer, CustomerProviderInterface $customerProvider)
    {
        $this->entity_manager = $entity_manager;
        $this->authorizationServer = $authorizationServer;
        $this->customerProvider = $customerProvider;
    }

    /**
     * @param Request $request
     * @return CustomerInterface|\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getCustomerByAccessTokenRequest(Request $request){

        $accessTokenInfo =  $this->authorizationServer->validateAuthenticatedRequest($request);

        if(!key_exists("oauth_access_token_id", $accessTokenInfo->getAttributes())){
            throw new HttpException(401, "AUTHENTICATION FAILED: REQUEST FAILED");
        }

        $accessTokenId = $accessTokenInfo->getAttribute("oauth_access_token_id");

        /**
         * @var AccessToken $accessToken
         */
        $accessToken = $this->entity_manager->getRepository(AccessToken::class)->findOneByIdentifier($accessTokenId);

        if(!$accessToken){
            throw new \Exception("AUTHENTICATION FAILED: ACCESS-TOKEN DOES NOT EXIST", 403);
        }
        else if((new \DateTime()) > $accessToken->getExpiryDateTime()){
            $this->entity_manager->remove($accessToken);
            $this->entity_manager->flush();
            throw new \Exception("AUTHENTICATION FAILED: ACCESS-TOKEN HAS EXPIRED", 401);
        }

        $customer = $this->customerProvider->getById($accessToken->getUserIdentifier());

        return $customer;
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getByAccessTokenRequest(Request $request){
        $customer = $this->getCustomerByAccessTokenRequest($request);

        return $this->getByCustomer($customer);
    }

    /**
     * @param CustomerInterface $customer
     * @return array
     */
    public function getByCustomer(CustomerInterface $customer): array
    {
        $oauthServerConfig = \Pimcore::getContainer()->getParameter("pimcore_customer_management_framework.oauth_server");

        if(key_exists("userExporter", $oauthServerConfig)){
            $userExporter = $oauthServerConfig["userExporter"];
            $fieldDefintions = $customer->getClass()->getFieldDefinitions();
            $result = [];
            foreach ($fieldDefintions as $fd) {
                if(in_array($fd->getName(),$userExporter)) {
                    $fieldName = $fd->getName();
                    $result[$fieldName] = $fd->getForWebserviceExport($customer);
                }
            }
            return $result;
        }

        return [];
    }

}
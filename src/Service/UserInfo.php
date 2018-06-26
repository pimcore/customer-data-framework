<?php
/**
 * Created by PhpStorm.
 * User: fbruenner
 * Date: 20.06.2018
 * Time: 15:06
 */

namespace CustomerManagementFrameworkBundle\Service;

use Pimcore\Tool\RestClient\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserInfo{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entity_manager = null;

    public function __construct(\Doctrine\ORM\EntityManager $entity_manager)
    {
        $this->entity_manager = $entity_manager;
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \League\OAuth2\Server\Exception\OAuthServerException
     */
    public function getByAccessTokenRequest(Request $request){

        /**
         * @var \CustomerManagementFrameworkBundle\Service\AuthorizationServer $authServerService
         */
        $authServerService = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Service\AuthorizationServer");

        $accessTokenInfo = $authServerService->validateAuthenticatedRequest($request);

        if(!key_exists("oauth_access_token_id", $accessTokenInfo->getAttributes())){
            throw new Exception("AUTHENTICATION FAILED: REQUEST FAILED");
        }

        $accessTokenId = $accessTokenInfo->getAttributes()["oauth_access_token_id"];

        /**
         * @var \CustomerManagementFrameworkBundle\Entity\Service\Auth\AccessToken $accessToken
         */
        $accessToken = $this->entity_manager->getRepository(\CustomerManagementFrameworkBundle\Entity\Service\Auth\AccessToken::class)->findOneByIdentifier($accessTokenId);

        if(!$accessToken){
            throw new HttpException(403, "AUTHENTICATION FAILED: ACCESS-TOKEN DOES NOT EXIST");
        }
        else if((new \DateTime()) > $accessToken->getExpiryDateTime()){
            $this->entity_manager->remove($accessToken);
            $this->entity_manager->flush();
            throw new HttpException(401, "AUTHENTICATION FAILED: ACCESS-TOKEN HAS EXPIRED");
        }

        $customerProvider = \Pimcore::getContainer()->get(\CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface::class);
        $customer = $customerProvider->getById($accessToken->getUserIdentifier());

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
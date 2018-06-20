<?php
/**
 * Created by PhpStorm.
 * User: fbruenner
 * Date: 20.06.2018
 * Time: 15:06
 */

namespace CustomerManagementFrameworkBundle\Service;

use Pimcore\Tool\RestClient\Exception;

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
     * @param string $accessToken
     * @return array
     * @throws Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getByAccessToken(string $accessToken){

        /**
         * @var \CustomerManagementFrameworkBundle\Entity\Service\Auth\AccessToken $accessToken
         */
        $accessToken = $this->entity_manager->getRepository(\CustomerManagementFrameworkBundle\Entity\Service\Auth\AccessToken::class)->findOneByIdentifier($accessToken);

        if(!$accessToken){
            throw new Exception("AUTHENTICATION FAILED");
        }
        else if((new \DateTime()) > $accessToken->getExpiryDateTime()){
            $this->entity_manager->remove($accessToken);
            $this->entity_manager->flush();
            throw new Exception("AUTHENTICATION FAILED: ACCESS-TOKEN HAS EXPIRED");
        }
        $customerProvider = \Pimcore::getContainer()->get(\CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface::class);
        $customer = $customerProvider->getById($accessToken->getUserIdentifier());

        $oauthServerConfig = \Pimcore::getContainer()->getParameter("pimcore_customer_management_framework.oauth_server");
        if(key_exists("user_exporter", $oauthServerConfig)){

            $userExporter = $oauthServerConfig["user_exporter"];
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
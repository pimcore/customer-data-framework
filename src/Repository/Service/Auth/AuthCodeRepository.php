<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace CustomerManagementFrameworkBundle\Repository\Service\Auth;

use CustomerManagementFrameworkBundle\Entity\Service\Auth\AuthCode;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository extends \Doctrine\ORM\EntityRepository implements AuthCodeRepositoryInterface
{

    /*
     * @var \Doctrine\ORM\EntityManager
     */
    private $entity_manager = null;

    /*
     * @var string
     */
    private $user_identifier = null;


    public function __construct()
    {
        $this->entity_manager = \Pimcore::getContainer()->get("doctrine.orm.entity_manager");
        parent::__construct($this->entity_manager, $this->entity_manager->getClassMetadata("CustomerManagementFrameworkBundle\Entity\Service\Auth\AuthCode"));
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        // Some logic here to save the auth code to a database
        $this->entity_manager->persist($authCodeEntity);
        $this->entity_manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId)
    {
        // Some logic to revoke the auth code in a database
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId)
    {
        return false; // The auth code has not been revoked
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode()
    {
        $entryFound = $this->entity_manager->getRepository(AuthCode::class)->findOneByUserIdentifier($this->user_identifier);
        if($entryFound)return $entryFound;

        return new AuthCode();
    }

    public function setUserIdenifier(string $userIdentifier){
        $this->user_identifier = $userIdentifier;
    }
}

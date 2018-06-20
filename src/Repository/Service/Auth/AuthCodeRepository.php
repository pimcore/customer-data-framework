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

    /**
     * @var \Doctrine\ORM\EntityManager $entity_manager
     */
    private $entity_manager = null;

    /**
     * @var string $user_identifier
     */
    private $user_identifier = null;

    /**
     * @var string $encryptionKey
     */
    private $encryption_key = null;


    public function __construct(\Doctrine\ORM\EntityManager $entity_manager)
    {
        $this->entity_manager = $entity_manager;
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

        $newAuthCode = new AuthCode();
        $newAuthCode->setEncryptionKey($this->encryption_key);
        return $newAuthCode;
    }

    public function setUserIdenifier(string $userIdentifier){
        $this->user_identifier = $userIdentifier;
    }

    public function setEncryptionKey(string $encryptionKey){
        $this->encryption_key = $encryptionKey;
    }
}

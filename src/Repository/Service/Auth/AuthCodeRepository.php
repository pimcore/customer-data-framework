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


    public function __construct(\Doctrine\ORM\EntityManager $entity_manager)
    {
        $this->entity_manager = $entity_manager;
        parent::__construct($this->entity_manager, $this->entity_manager->getClassMetadata("CustomerManagementFrameworkBundle\Entity\Service\Auth\AuthCode"));
    }

    /**
     * {@inheritdoc}
     * @param AuthCodeEntityInterface $authCodeEntity
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        // Some logic here to save the auth code to a database
        $this->entity_manager->persist($authCodeEntity);
        $this->entity_manager->flush();
    }

    /**
     * {@inheritdoc}
     * @param string $codeId
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function revokeAuthCode($codeId)
    {
        $authCode = $this->entity_manager->getRepository(AuthCode::class)->findOneByIdentifier($codeId);
        if($authCode) {
            $this->entity_manager->remove($authCode);
            $this->entity_manager->flush();
        }
    }

    /**
     * {@inheritdoc}
     * @param string $codeId
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     */
    public function isAuthCodeRevoked($codeId)
    {
        /**
         * @var AuthCode $authCode
         */
        $authCode = $this->entity_manager->getRepository(AuthCode::class)->findOneByIdentifier($codeId);
        return !$authCode || ($authCode && (new \DateTime() > $authCode->getExpiryDateTime()));
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode()
    {
        $newAuthCode = new AuthCode();
        return $newAuthCode;
    }

}

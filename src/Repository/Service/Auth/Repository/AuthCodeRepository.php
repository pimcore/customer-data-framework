<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace CustomerManagementFrameworkBundle\Repository\Service\Auth\Repository;

use CustomerManagementFrameworkBundle\Entity\Service\Auth\Entity\AuthCode;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository extends \Doctrine\ORM\EntityRepository implements AuthCodeRepositoryInterface
{

    /*
     * @var \Doctrine\ORM\EntityManager
     */
    private $entity_manager = null;

    public function __construct()
    {
        $this->entity_manager = \Pimcore::getContainer()->get("doctrine.orm.entity_manager");
        parent::__construct($this->entity_manager, $this->entity_manager->getClassMetadata("CustomerManagementFrameworkBundle\Entity\Service\Auth\Entity\AccessToken"));
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $accessTokenEntity)
    {
        // Some logic here to save the access token to a database
        $this->entity_manager->persist($accessTokenEntity);
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
        return new AuthCode();
    }
}

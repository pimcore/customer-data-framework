<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace CustomerManagementFrameworkBundle\Repository\Service\Auth;

use CustomerManagementFrameworkBundle\Entity\Service\Auth\RefreshToken;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository extends \Doctrine\ORM\EntityRepository implements RefreshTokenRepositoryInterface
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entity_manager = null;

    public function __construct(\Doctrine\ORM\EntityManager $entity_manager)
    {
        $this->entity_manager = $entity_manager;
        parent::__construct($entity_manager, $entity_manager->getClassMetadata("CustomerManagementFrameworkBundle\Entity\Service\Auth\RefreshToken"));
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntityInterface)
    {
        // Some logic here to save the access token to a database
        $this->entity_manager->persist($refreshTokenEntityInterface);
        $this->entity_manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId)
    {
        // Some logic to revoke the refresh token in a database
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        return false; // The refresh token has not been revoked
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken()
    {
        return new RefreshToken();
    }
}

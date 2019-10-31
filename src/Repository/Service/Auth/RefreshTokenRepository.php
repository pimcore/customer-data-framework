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
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository extends \Doctrine\ORM\EntityRepository implements RefreshTokenRepositoryInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entity_manager = null;

    public function __construct(EntityManagerInterface $entity_manager)
    {
        $this->entity_manager = $entity_manager;
        parent::__construct($entity_manager, $entity_manager->getClassMetadata("CustomerManagementFrameworkBundle\Entity\Service\Auth\RefreshToken"));
    }

    /**
     * {@inheritdoc}
     * @param RefreshTokenEntityInterface $refreshTokenEntityInterface
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntityInterface)
    {
        // Some logic here to save the access token to a database
        $this->entity_manager->persist($refreshTokenEntityInterface);
        $this->entity_manager->flush();
    }

    /**
     * {@inheritdoc}
     * @param string $tokenId
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function revokeRefreshToken($tokenId)
    {
        /**
         * @var RefreshToken $refreshToken
         */
        $refreshToken = $this->entity_manager->getRepository(RefreshToken::class)->findOneByIdentifier($tokenId);
        if($refreshToken) {
            $this->entity_manager->remove($refreshToken);
            $this->entity_manager->flush();
        }
    }

    /**
     * {@inheritdoc}
     * @param string $tokenId
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        /**
         * @var RefreshToken $refreshToken
         */
        $refreshToken = $this->entity_manager->getRepository(RefreshToken::class)->findOneByIdentifier($tokenId);
        return !$refreshToken || ($refreshToken && (new \DateTime() > $refreshToken->getExpiryDateTime()));
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken()
    {
        return new RefreshToken();
    }
}

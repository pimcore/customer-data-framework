<?php

namespace CustomerManagementFrameworkBundle\Entity\Service\Auth;

use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

/**
 * RefreshToken
 *
 * @ORM\Table(name="plugin_cmf_auth_entity_refresh_token")
 * @ORM\Entity(repositoryClass="CustomerManagementFrameworkBundle\Repository\Service\Auth\RefreshTokenRepository")
 */
class RefreshToken implements \League\OAuth2\Server\Entities\RefreshTokenEntityInterface
{
    use RefreshTokenTrait, EntityTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="accessTokenIdentifier", type="string", length=255)
     */
    protected $accessTokenIdentifier;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiryDateTime", type="datetime")
     */
    protected $expiryDateTime;

    /**
     * @var string
     *
     * @ORM\Column(name="identifier", type="text")
     */
    protected $identifier;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set expiryDateTime
     *
     * @param \DateTime $dateTime
     *
     * @return RefreshToken
     */
    public function setExpiryDateTime(\DateTime $dateTime)
    {
        $this->expiryDateTime = $dateTime;

        return $this;
    }

    /**
     * Get expiryDateTime
     *
     * @return \DateTime
     */
    public function getExpiryDateTime()
    {
        return $this->expiryDateTime;
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     *
     * @return RefreshToken
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set identifier
     *
     * @param string $accessTokenIdentifier
     *
     * @return RefreshToken
     */
    public function setAccessToken(AccessTokenEntityInterface $accessTokenIdentifier)
    {
        $this->accessTokenIdentifier = $accessTokenIdentifier->getIdentifier();

        return $this;
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessTokenIdentifier;
    }

}


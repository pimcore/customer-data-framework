<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace CustomerManagementFrameworkBundle\Entity\Service\Auth;

use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use League\OAuth2\Server\Entities\ClientEntityInterface;

/**
 * AuthCode
 *
 * @ORM\Table(name="plugin_cmf_auth_entity_auth_code")
 * @ORM\Entity(repositoryClass="CustomerManagementFrameworkBundle\Repository\Service\Auth\AuthCodeRepository")
 */
class AuthCode implements AuthCodeEntityInterface
{
    use EntityTrait, TokenEntityTrait, AuthCodeTrait;

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
     * @ORM\Column(name="identifier", type="text")
     */
    protected $identifier;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiryDateTime", type="datetime")
     */
    protected $expiryDateTime;

    /**
     * @var string
     *
     * @ORM\Column(name="userIdentifier", type="string", length=255)
     */
    protected $userIdentifier;

    /**
     * @var string
     *
     * @ORM\Column(name="redirectUri", type="string", length=255)
     */
    protected $redirectUri;


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
     * Set identifier
     *
     * @param string $identifier
     *
     * @return AuthCode
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
     * Set expiryDateTime
     *
     * @param \DateTime $expiryDateTime
     *
     * @return AuthCode
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
     * Set userIdentifier
     *
     * @param string $userIdentifier
     *
     * @return AuthCode
     */
    public function setUserIdentifier($userIdentifier)
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    /**
     * Get userIdentifier
     *
     * @return string
     */
    public function getUserIdentifier()
    {
        return $this->userIdentifier;
    }

    /**
     * Set redirectUri
     *
     * @param string $redirectUri
     *
     * @return AuthCode
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    /**
     * Get redirectUri
     *
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

}

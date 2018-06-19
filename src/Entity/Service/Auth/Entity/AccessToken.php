<?php

namespace CustomerManagementFrameworkBundle\Entity\Service\Auth\Entity;

use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

/**
 * AccessToken
 *
 * @ORM\Table(name="plugin_cmf_auth_entity_access_token")
 * @ORM\Entity(repositoryClass="CustomerManagementFrameworkBundle\Repository\Service\Auth\Repository\AccessTokenRepository")
 */
class AccessToken implements \League\OAuth2\Server\Entities\AccessTokenEntityInterface
{

    use AccessTokenTrait, TokenEntityTrait, EntityTrait;

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
     * @ORM\Column(name="userIdentifier", type="string", length=255)
     */
    protected $userIdentifier;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiryDateTime", type="datetime")
     */
    protected $expiryDateTime;

    /**
     * @var ClientEntityInterface
     */
    protected $client;

    /**
     * @var string
     *
     * @ORM\Column(name="client", type="string", length=255)
     */
    protected $clientIdentifier;

    /**
     * @var string
     *
     * @ORM\Column(name="identifier", type="string", length=255)
     */
    protected $identifier;


    protected $scopes = [];


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
     * Set userIdentifier
     *
     * @param string $userIdentifier
     *
     * @return AccessToken
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
     * Set expiryDateTime
     *
     * @param \DateTime $expiryDateTime
     *
     * @return AccessToken
     */
    public function setExpiryDateTime(\DateTime $expiryDateTime)
    {
        $this->expiryDateTime = $expiryDateTime;

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
     * Set client
     *
     * @param string $client
     *
     * @return AccessToken
     */
    public function setClient(ClientEntityInterface $client)
    {
        $this->client = $client;
        $this->clientIdentifier = $client->getIdentifier();

        return $this;
    }

    /**
     * Get client
     *
     * @return ClientEntityInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getClientIdentifier(): string
    {
        return $this->clientIdentifier;
    }



    /**
     * Set identifier
     *
     * @param string $identifier
     *
     * @return AccessToken
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
     * Associate a scope with the token.
     *
     * @param ScopeEntityInterface $scope
     */
    public function addScope(ScopeEntityInterface $scope)
    {
        $this->scopes[$scope->getIdentifier()] = $scope;
    }

    /**
     * Return an array of scopes associated with the token.
     *
     * @return ScopeEntityInterface[]
     */
    public function getScopes()
    {
        return array_values($this->scopes);
    }

}


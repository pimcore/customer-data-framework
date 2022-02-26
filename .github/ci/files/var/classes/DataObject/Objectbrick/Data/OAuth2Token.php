<?php

/**
Fields Summary:
- accessToken [textarea]
- tokenType [input]
- expiresAt [input]
- refreshToken [textarea]
- scope [input]
*/

namespace Pimcore\Model\DataObject\Objectbrick\Data;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Exception\InheritanceParentNotFoundException;
use Pimcore\Model\DataObject\PreGetValueHookInterface;


class OAuth2Token extends \CustomerManagementFrameworkBundle\Model\Objectbrick\AbstractOAuth2Token
{
protected $type = "OAuth2Token";
protected $accessToken;
protected $tokenType;
protected $expiresAt;
protected $refreshToken;
protected $scope;


/**
* OAuth2Token constructor.
* @param DataObject\Concrete $object
*/
public function __construct(DataObject\Concrete $object)
{
	parent::__construct($object);
	$this->markFieldDirty("_self");
}


/**
* Get accessToken - accessToken
* @return string|null
*/
public function getAccessToken(): ?string
{
	$data = $this->accessToken;
	if(\Pimcore\Model\DataObject::doGetInheritedValues($this->getObject()) && $this->getDefinition()->getFieldDefinition("accessToken")->isEmpty($data)) {
		try {
			return $this->getValueFromParent("accessToken");
		} catch (InheritanceParentNotFoundException $e) {
			// no data from parent available, continue ...
		}
	}
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set accessToken - accessToken
* @param string|null $accessToken
* @return \Pimcore\Model\DataObject\Objectbrick\Data\OAuth2Token
*/
public function setAccessToken (?string $accessToken)
{
	$this->accessToken = $accessToken;

	return $this;
}

/**
* Get tokenType - tokenType
* @return string|null
*/
public function getTokenType(): ?string
{
	$data = $this->tokenType;
	if(\Pimcore\Model\DataObject::doGetInheritedValues($this->getObject()) && $this->getDefinition()->getFieldDefinition("tokenType")->isEmpty($data)) {
		try {
			return $this->getValueFromParent("tokenType");
		} catch (InheritanceParentNotFoundException $e) {
			// no data from parent available, continue ...
		}
	}
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set tokenType - tokenType
* @param string|null $tokenType
* @return \Pimcore\Model\DataObject\Objectbrick\Data\OAuth2Token
*/
public function setTokenType (?string $tokenType)
{
	$this->tokenType = $tokenType;

	return $this;
}

/**
* Get expiresAt - expiresAt
* @return string|null
*/
public function getExpiresAt(): ?string
{
	$data = $this->expiresAt;
	if(\Pimcore\Model\DataObject::doGetInheritedValues($this->getObject()) && $this->getDefinition()->getFieldDefinition("expiresAt")->isEmpty($data)) {
		try {
			return $this->getValueFromParent("expiresAt");
		} catch (InheritanceParentNotFoundException $e) {
			// no data from parent available, continue ...
		}
	}
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set expiresAt - expiresAt
* @param string|null $expiresAt
* @return \Pimcore\Model\DataObject\Objectbrick\Data\OAuth2Token
*/
public function setExpiresAt (?string $expiresAt)
{
	$this->expiresAt = $expiresAt;

	return $this;
}

/**
* Get refreshToken - refreshToken
* @return string|null
*/
public function getRefreshToken(): ?string
{
	$data = $this->refreshToken;
	if(\Pimcore\Model\DataObject::doGetInheritedValues($this->getObject()) && $this->getDefinition()->getFieldDefinition("refreshToken")->isEmpty($data)) {
		try {
			return $this->getValueFromParent("refreshToken");
		} catch (InheritanceParentNotFoundException $e) {
			// no data from parent available, continue ...
		}
	}
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set refreshToken - refreshToken
* @param string|null $refreshToken
* @return \Pimcore\Model\DataObject\Objectbrick\Data\OAuth2Token
*/
public function setRefreshToken (?string $refreshToken)
{
	$this->refreshToken = $refreshToken;

	return $this;
}

/**
* Get scope - scope
* @return string|null
*/
public function getScope(): ?string
{
	$data = $this->scope;
	if(\Pimcore\Model\DataObject::doGetInheritedValues($this->getObject()) && $this->getDefinition()->getFieldDefinition("scope")->isEmpty($data)) {
		try {
			return $this->getValueFromParent("scope");
		} catch (InheritanceParentNotFoundException $e) {
			// no data from parent available, continue ...
		}
	}
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set scope - scope
* @param string|null $scope
* @return \Pimcore\Model\DataObject\Objectbrick\Data\OAuth2Token
*/
public function setScope (?string $scope)
{
	$this->scope = $scope;

	return $this;
}

}

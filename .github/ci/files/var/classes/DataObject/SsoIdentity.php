<?php

/**
* Inheritance: no
* Variants: no


Fields Summary:
- provider [input]
- identifier [input]
- profileData [textarea]
- credentials [objectbricks]
*/

namespace Pimcore\Model\DataObject;

use Pimcore\Model\DataObject\Exception\InheritanceParentNotFoundException;
use Pimcore\Model\DataObject\PreGetValueHookInterface;

/**
* @method static \Pimcore\Model\DataObject\SsoIdentity\Listing getList()
* @method static \Pimcore\Model\DataObject\SsoIdentity\Listing|\Pimcore\Model\DataObject\SsoIdentity|null getByProvider($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\SsoIdentity\Listing|\Pimcore\Model\DataObject\SsoIdentity|null getByIdentifier($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\SsoIdentity\Listing|\Pimcore\Model\DataObject\SsoIdentity|null getByProfileData($value, $limit = 0, $offset = 0, $objectTypes = null)
*/

class SsoIdentity extends \CustomerManagementFrameworkBundle\Model\AbstractSsoIdentity
{
protected $o_classId = "3";
protected $o_className = "SsoIdentity";
protected $provider;
protected $identifier;
protected $profileData;
protected $credentials;


/**
* @param array $values
* @return \Pimcore\Model\DataObject\SsoIdentity
*/
public static function create($values = array()) {
	$object = new static();
	$object->setValues($values);
	return $object;
}

/**
* Get provider - Provider
* @return string|null
*/
public function getProvider(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("provider");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->provider;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set provider - Provider
* @param string|null $provider
* @return \Pimcore\Model\DataObject\SsoIdentity
*/
public function setProvider(?string $provider)
{
	$this->provider = $provider;

	return $this;
}

/**
* Get identifier - Identifier
* @return string|null
*/
public function getIdentifier(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("identifier");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->identifier;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set identifier - Identifier
* @param string|null $identifier
* @return \Pimcore\Model\DataObject\SsoIdentity
*/
public function setIdentifier(?string $identifier)
{
	$this->identifier = $identifier;

	return $this;
}

/**
* Get profileData - Profile Data
* @return string|null
*/
public function getProfileData(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("profileData");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->profileData;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set profileData - Profile Data
* @param string|null $profileData
* @return \Pimcore\Model\DataObject\SsoIdentity
*/
public function setProfileData(?string $profileData)
{
	$this->profileData = $profileData;

	return $this;
}

/**
* @return \Pimcore\Model\DataObject\SsoIdentity\Credentials
*/
public function getCredentials(): ?\Pimcore\Model\DataObject\Objectbrick
{
	$data = $this->credentials;
	if (!$data) {
		if (\Pimcore\Tool::classExists("\\Pimcore\\Model\\DataObject\\SsoIdentity\\Credentials")) {
			$data = new \Pimcore\Model\DataObject\SsoIdentity\Credentials($this, "credentials");
			$this->credentials = $data;
		} else {
			return null;
		}
	}
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("credentials");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	return $data;
}

/**
* Set credentials - Credentials
* @param \Pimcore\Model\DataObject\Objectbrick|null $credentials
* @return \Pimcore\Model\DataObject\SsoIdentity
*/
public function setCredentials(?\Pimcore\Model\DataObject\Objectbrick $credentials)
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\Objectbricks $fd */
	$fd = $this->getClass()->getFieldDefinition("credentials");
	$this->credentials = $fd->preSetData($this, $credentials);
	return $this;
}

}


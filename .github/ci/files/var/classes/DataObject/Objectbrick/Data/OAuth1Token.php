<?php

/**
Fields Summary:
- token [textarea]
- tokenSecret [textarea]
*/

namespace Pimcore\Model\DataObject\Objectbrick\Data;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Exception\InheritanceParentNotFoundException;
use Pimcore\Model\DataObject\PreGetValueHookInterface;


class OAuth1Token extends \CustomerManagementFrameworkBundle\Model\Objectbrick\AbstractOAuth1Token
{
protected $type = "OAuth1Token";
protected $token;
protected $tokenSecret;


/**
* OAuth1Token constructor.
* @param DataObject\Concrete $object
*/
public function __construct(DataObject\Concrete $object)
{
	parent::__construct($object);
	$this->markFieldDirty("_self");
}


/**
* Get token - token
* @return string|null
*/
public function getToken(): ?string
{
	$data = $this->token;
	if(\Pimcore\Model\DataObject::doGetInheritedValues($this->getObject()) && $this->getDefinition()->getFieldDefinition("token")->isEmpty($data)) {
		try {
			return $this->getValueFromParent("token");
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
* Set token - token
* @param string|null $token
* @return \Pimcore\Model\DataObject\Objectbrick\Data\OAuth1Token
*/
public function setToken (?string $token)
{
	$this->token = $token;

	return $this;
}

/**
* Get tokenSecret - tokenSecret
* @return string|null
*/
public function getTokenSecret(): ?string
{
	$data = $this->tokenSecret;
	if(\Pimcore\Model\DataObject::doGetInheritedValues($this->getObject()) && $this->getDefinition()->getFieldDefinition("tokenSecret")->isEmpty($data)) {
		try {
			return $this->getValueFromParent("tokenSecret");
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
* Set tokenSecret - tokenSecret
* @param string|null $tokenSecret
* @return \Pimcore\Model\DataObject\Objectbrick\Data\OAuth1Token
*/
public function setTokenSecret (?string $tokenSecret)
{
	$this->tokenSecret = $tokenSecret;

	return $this;
}

}

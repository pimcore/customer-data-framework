<?php

namespace Pimcore\Model\DataObject\SsoIdentity;

use Pimcore\Model\DataObject;

/**
 * @method DataObject\SsoIdentity|false current()
 * @method DataObject\SsoIdentity[] load()
 * @method DataObject\SsoIdentity[] getData()
 */

class Listing extends DataObject\Listing\Concrete
{
protected $classId = "3";
protected $className = "SsoIdentity";


/**
* Filter by provider (Provider)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByProvider ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("provider")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by identifier (Identifier)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByIdentifier ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("identifier")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by profileData (Profile Data)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByProfileData ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("profileData")->addListingFilter($this, $data, $operator);
	return $this;
}



}

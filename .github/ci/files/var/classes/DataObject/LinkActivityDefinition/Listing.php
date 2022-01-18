<?php

namespace Pimcore\Model\DataObject\LinkActivityDefinition;

use Pimcore\Model\DataObject;

/**
 * @method DataObject\LinkActivityDefinition|false current()
 * @method DataObject\LinkActivityDefinition[] load()
 * @method DataObject\LinkActivityDefinition[] getData()
 */

class Listing extends DataObject\Listing\Concrete
{
protected $classId = "5";
protected $className = "LinkActivityDefinition";


/**
* Filter by code (code (cmfa))
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByCode ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("code")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by attributeType (type)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByAttributeType ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("attributeType")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by label (label)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByLabel ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("label")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by active (active)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByActive ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("active")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by utm_source (Campaign Source (utm_source))
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByUtm_source ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("utm_source")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by utm_medium (Campaign Medium (utm_medium))
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByUtm_medium ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("utm_medium")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by utm_campaign (Campaign Name (utm_campaign))
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByUtm_campaign ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("utm_campaign")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by utm_term (Campaign Term (utm_term))
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByUtm_term ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("utm_term")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by utm_content (Campaign Content (utm_content))
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByUtm_content ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("utm_content")->addListingFilter($this, $data, $operator);
	return $this;
}



}

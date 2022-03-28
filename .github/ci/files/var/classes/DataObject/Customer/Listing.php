<?php

namespace Pimcore\Model\DataObject\Customer;

use Pimcore\Model\DataObject;

/**
 * @method DataObject\Customer|false current()
 * @method DataObject\Customer[] load()
 * @method DataObject\Customer[] getData()
 */

class Listing extends DataObject\Listing\Concrete
{
protected $classId = "CU";
protected $className = "Customer";


/**
* Filter by active (Active)
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
* Filter by gender (Gender)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByGender ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("gender")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by firstname (Firstname)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByFirstname ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("firstname")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by lastname (Lastname)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByLastname ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("lastname")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by company (Company)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByCompany ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("company")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by email (Email)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByEmail ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("email")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by street (Street)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByStreet ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("street")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by zip (Zip)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByZip ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("zip")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by city (City)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByCity ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("city")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by countryCode (Country)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByCountryCode ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("countryCode")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by phone (phone)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByPhone ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("phone")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by idEncoded (Id Encoded)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByIdEncoded ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("idEncoded")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by customerLanguage (Language)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByCustomerLanguage ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("customerLanguage")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by newsletterConfirmed (Newsletter Confirmed)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByNewsletterConfirmed ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("newsletterConfirmed")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by newsletterConfirmToken (Newsletter Confirm Token)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByNewsletterConfirmToken ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("newsletterConfirmToken")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by manualSegments (Manual Segments)
* @param mixed $data
* @param string $operator SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByManualSegments ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("manualSegments")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by calculatedSegments (Calculated Segments)
* @param mixed $data
* @param string $operator SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByCalculatedSegments ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("calculatedSegments")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by ssoIdentities (SSO Identities)
* @param mixed $data
* @param string $operator SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterBySsoIdentities ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("ssoIdentities")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by passwordRecoveryToken (Password Recovery Token)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByPasswordRecoveryToken ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("passwordRecoveryToken")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by passwordRecoveryTokenDate (Password Recovery Token Date)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByPasswordRecoveryTokenDate ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("passwordRecoveryTokenDate")->addListingFilter($this, $data, $operator);
	return $this;
}



}

<?php

/**
* Inheritance: no
* Variants: no


Fields Summary:
- active [checkbox]
- gender [gender]
- firstname [firstname]
- lastname [lastname]
- company [input]
- email [email]
- street [input]
- zip [input]
- city [input]
- countryCode [country]
- phone [input]
- idEncoded [input]
- customerLanguage [language]
- newsletter [consent]
- newsletterConfirmed [newsletterConfirmed]
- newsletterConfirmToken [input]
- profiling [consent]
- manualSegments [advancedManyToManyObjectRelation]
- calculatedSegments [advancedManyToManyObjectRelation]
- password [password]
- ssoIdentities [manyToManyObjectRelation]
- passwordRecoveryToken [input]
- passwordRecoveryTokenDate [datetime]
*/

namespace Pimcore\Model\DataObject;

use Pimcore\Model\DataObject\Exception\InheritanceParentNotFoundException;
use Pimcore\Model\DataObject\PreGetValueHookInterface;

/**
* @method static \Pimcore\Model\DataObject\Customer\Listing getList()
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByActive($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByGender($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByFirstname($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByLastname($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByCompany($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByEmail($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByStreet($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByZip($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByCity($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByCountryCode($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByPhone($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByIdEncoded($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByCustomerLanguage($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByNewsletterConfirmed($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByNewsletterConfirmToken($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByManualSegments($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByCalculatedSegments($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getBySsoIdentities($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByPasswordRecoveryToken($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Customer\Listing|\Pimcore\Model\DataObject\Customer|null getByPasswordRecoveryTokenDate($value, $limit = 0, $offset = 0, $objectTypes = null)
*/

class Customer extends \CustomerManagementFrameworkBundle\Model\AbstractCustomer\DefaultAbstractUserawareCustomer
{
protected $o_classId = "CU";
protected $o_className = "Customer";
protected $active;
protected $gender;
protected $firstname;
protected $lastname;
protected $company;
protected $email;
protected $street;
protected $zip;
protected $city;
protected $countryCode;
protected $phone;
protected $idEncoded;
protected $customerLanguage;
protected $newsletter;
protected $newsletterConfirmed;
protected $newsletterConfirmToken;
protected $profiling;
protected $manualSegments;
protected $calculatedSegments;
protected $password;
protected $ssoIdentities;
protected $passwordRecoveryToken;
protected $passwordRecoveryTokenDate;


/**
* @param array $values
* @return \Pimcore\Model\DataObject\Customer
*/
public static function create($values = array()) {
	$object = new static();
	$object->setValues($values);
	return $object;
}

/**
* Get active - Active
* @return bool|null
*/
public function getActive(): ?bool
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("active");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->active;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set active - Active
* @param bool|null $active
* @return \Pimcore\Model\DataObject\Customer
*/
public function setActive(?bool $active)
{
	$this->active = $active;

	return $this;
}

/**
* Get gender - Gender
* @return string|null
*/
public function getGender(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("gender");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->gender;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set gender - Gender
* @param string|null $gender
* @return \Pimcore\Model\DataObject\Customer
*/
public function setGender(?string $gender)
{
	$this->gender = $gender;

	return $this;
}

/**
* Get firstname - Firstname
* @return string|null
*/
public function getFirstname(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("firstname");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->firstname;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set firstname - Firstname
* @param string|null $firstname
* @return \Pimcore\Model\DataObject\Customer
*/
public function setFirstname(?string $firstname)
{
	$this->firstname = $firstname;

	return $this;
}

/**
* Get lastname - Lastname
* @return string|null
*/
public function getLastname(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("lastname");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->lastname;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set lastname - Lastname
* @param string|null $lastname
* @return \Pimcore\Model\DataObject\Customer
*/
public function setLastname(?string $lastname)
{
	$this->lastname = $lastname;

	return $this;
}

/**
* Get company - Company
* @return string|null
*/
public function getCompany(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("company");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->company;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set company - Company
* @param string|null $company
* @return \Pimcore\Model\DataObject\Customer
*/
public function setCompany(?string $company)
{
	$this->company = $company;

	return $this;
}

/**
* Get email - Email
* @return string|null
*/
public function getEmail(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("email");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->email;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set email - Email
* @param string|null $email
* @return \Pimcore\Model\DataObject\Customer
*/
public function setEmail(?string $email)
{
	$this->email = $email;

	return $this;
}

/**
* Get street - Street
* @return string|null
*/
public function getStreet(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("street");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->street;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set street - Street
* @param string|null $street
* @return \Pimcore\Model\DataObject\Customer
*/
public function setStreet(?string $street)
{
	$this->street = $street;

	return $this;
}

/**
* Get zip - Zip
* @return string|null
*/
public function getZip(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("zip");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->zip;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set zip - Zip
* @param string|null $zip
* @return \Pimcore\Model\DataObject\Customer
*/
public function setZip(?string $zip)
{
	$this->zip = $zip;

	return $this;
}

/**
* Get city - City
* @return string|null
*/
public function getCity(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("city");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->city;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set city - City
* @param string|null $city
* @return \Pimcore\Model\DataObject\Customer
*/
public function setCity(?string $city)
{
	$this->city = $city;

	return $this;
}

/**
* Get countryCode - Country
* @return string|null
*/
public function getCountryCode(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("countryCode");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->countryCode;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set countryCode - Country
* @param string|null $countryCode
* @return \Pimcore\Model\DataObject\Customer
*/
public function setCountryCode(?string $countryCode)
{
	$this->countryCode = $countryCode;

	return $this;
}

/**
* Get phone - phone
* @return string|null
*/
public function getPhone(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("phone");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->phone;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set phone - phone
* @param string|null $phone
* @return \Pimcore\Model\DataObject\Customer
*/
public function setPhone(?string $phone)
{
	$this->phone = $phone;

	return $this;
}

/**
* Get idEncoded - Id Encoded
* @return string|null
*/
public function getIdEncoded(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("idEncoded");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->idEncoded;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set idEncoded - Id Encoded
* @param string|null $idEncoded
* @return \Pimcore\Model\DataObject\Customer
*/
public function setIdEncoded(?string $idEncoded)
{
	$this->idEncoded = $idEncoded;

	return $this;
}

/**
* Get customerLanguage - Language
* @return string|null
*/
public function getCustomerLanguage(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("customerLanguage");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->customerLanguage;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set customerLanguage - Language
* @param string|null $customerLanguage
* @return \Pimcore\Model\DataObject\Customer
*/
public function setCustomerLanguage(?string $customerLanguage)
{
	$this->customerLanguage = $customerLanguage;

	return $this;
}

/**
* Get newsletter - Newsletter
* @return \Pimcore\Model\DataObject\Data\Consent|null
*/
public function getNewsletter(): ?\Pimcore\Model\DataObject\Data\Consent
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("newsletter");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->newsletter;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set newsletter - Newsletter
* @param \Pimcore\Model\DataObject\Data\Consent|null $newsletter
* @return \Pimcore\Model\DataObject\Customer
*/
public function setNewsletter(?\Pimcore\Model\DataObject\Data\Consent $newsletter)
{
	$this->newsletter = $newsletter;

	return $this;
}

/**
* Get newsletterConfirmed - Newsletter Confirmed
* @return bool|null
*/
public function getNewsletterConfirmed(): ?bool
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("newsletterConfirmed");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->newsletterConfirmed;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set newsletterConfirmed - Newsletter Confirmed
* @param bool|null $newsletterConfirmed
* @return \Pimcore\Model\DataObject\Customer
*/
public function setNewsletterConfirmed(?bool $newsletterConfirmed)
{
	$this->newsletterConfirmed = $newsletterConfirmed;

	return $this;
}

/**
* Get newsletterConfirmToken - Newsletter Confirm Token
* @return string|null
*/
public function getNewsletterConfirmToken(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("newsletterConfirmToken");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->newsletterConfirmToken;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set newsletterConfirmToken - Newsletter Confirm Token
* @param string|null $newsletterConfirmToken
* @return \Pimcore\Model\DataObject\Customer
*/
public function setNewsletterConfirmToken(?string $newsletterConfirmToken)
{
	$this->newsletterConfirmToken = $newsletterConfirmToken;

	return $this;
}

/**
* Get profiling - Profiling
* @return \Pimcore\Model\DataObject\Data\Consent|null
*/
public function getProfiling(): ?\Pimcore\Model\DataObject\Data\Consent
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("profiling");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->profiling;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set profiling - Profiling
* @param \Pimcore\Model\DataObject\Data\Consent|null $profiling
* @return \Pimcore\Model\DataObject\Customer
*/
public function setProfiling(?\Pimcore\Model\DataObject\Data\Consent $profiling)
{
	$this->profiling = $profiling;

	return $this;
}

/**
* Get manualSegments - Manual Segments
* @return \Pimcore\Model\DataObject\Data\ObjectMetadata[]
*/
public function getManualSegments(): array
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("manualSegments");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getClass()->getFieldDefinition("manualSegments")->preGetData($this);

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set manualSegments - Manual Segments
* @param \Pimcore\Model\DataObject\Data\ObjectMetadata[] $manualSegments
* @return \Pimcore\Model\DataObject\Customer
*/
public function setManualSegments(?array $manualSegments)
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation $fd */
	$fd = $this->getClass()->getFieldDefinition("manualSegments");
	$hideUnpublished = \Pimcore\Model\DataObject\Concrete::getHideUnpublished();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished(false);
	$currentData = $this->getManualSegments();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished($hideUnpublished);
	$isEqual = $fd->isEqual($currentData, $manualSegments);
	if (!$isEqual) {
		$this->markFieldDirty("manualSegments", true);
	}
	$this->manualSegments = $fd->preSetData($this, $manualSegments);
	return $this;
}

/**
* Get calculatedSegments - Calculated Segments
* @return \Pimcore\Model\DataObject\Data\ObjectMetadata[]
*/
public function getCalculatedSegments(): array
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("calculatedSegments");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getClass()->getFieldDefinition("calculatedSegments")->preGetData($this);

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set calculatedSegments - Calculated Segments
* @param \Pimcore\Model\DataObject\Data\ObjectMetadata[] $calculatedSegments
* @return \Pimcore\Model\DataObject\Customer
*/
public function setCalculatedSegments(?array $calculatedSegments)
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation $fd */
	$fd = $this->getClass()->getFieldDefinition("calculatedSegments");
	$hideUnpublished = \Pimcore\Model\DataObject\Concrete::getHideUnpublished();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished(false);
	$currentData = $this->getCalculatedSegments();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished($hideUnpublished);
	$isEqual = $fd->isEqual($currentData, $calculatedSegments);
	if (!$isEqual) {
		$this->markFieldDirty("calculatedSegments", true);
	}
	$this->calculatedSegments = $fd->preSetData($this, $calculatedSegments);
	return $this;
}

/**
* Get password - Password
* @return string|null
*/
public function getPassword(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("password");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->password;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set password - Password
* @param string|null $password
* @return \Pimcore\Model\DataObject\Customer
*/
public function setPassword(?string $password)
{
	$this->password = $password;

	return $this;
}

/**
* Get ssoIdentities - SSO Identities
* @return \Pimcore\Model\DataObject\SsoIdentity[]
*/
public function getSsoIdentities(): array
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("ssoIdentities");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getClass()->getFieldDefinition("ssoIdentities")->preGetData($this);

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set ssoIdentities - SSO Identities
* @param \Pimcore\Model\DataObject\SsoIdentity[] $ssoIdentities
* @return \Pimcore\Model\DataObject\Customer
*/
public function setSsoIdentities(?array $ssoIdentities)
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyObjectRelation $fd */
	$fd = $this->getClass()->getFieldDefinition("ssoIdentities");
	$hideUnpublished = \Pimcore\Model\DataObject\Concrete::getHideUnpublished();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished(false);
	$currentData = $this->getSsoIdentities();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished($hideUnpublished);
	$isEqual = $fd->isEqual($currentData, $ssoIdentities);
	if (!$isEqual) {
		$this->markFieldDirty("ssoIdentities", true);
	}
	$this->ssoIdentities = $fd->preSetData($this, $ssoIdentities);
	return $this;
}

/**
* Get passwordRecoveryToken - Password Recovery Token
* @return string|null
*/
public function getPasswordRecoveryToken(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("passwordRecoveryToken");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->passwordRecoveryToken;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set passwordRecoveryToken - Password Recovery Token
* @param string|null $passwordRecoveryToken
* @return \Pimcore\Model\DataObject\Customer
*/
public function setPasswordRecoveryToken(?string $passwordRecoveryToken)
{
	$this->passwordRecoveryToken = $passwordRecoveryToken;

	return $this;
}

/**
* Get passwordRecoveryTokenDate - Password Recovery Token Date
* @return \Carbon\Carbon|null
*/
public function getPasswordRecoveryTokenDate(): ?\Carbon\Carbon
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("passwordRecoveryTokenDate");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->passwordRecoveryTokenDate;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set passwordRecoveryTokenDate - Password Recovery Token Date
* @param \Carbon\Carbon|null $passwordRecoveryTokenDate
* @return \Pimcore\Model\DataObject\Customer
*/
public function setPasswordRecoveryTokenDate(?\Carbon\Carbon $passwordRecoveryTokenDate)
{
	$this->passwordRecoveryTokenDate = $passwordRecoveryTokenDate;

	return $this;
}

}

